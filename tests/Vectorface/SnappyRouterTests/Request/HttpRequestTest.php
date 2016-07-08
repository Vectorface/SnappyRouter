<?php

namespace Vectorface\SnappyRouterTests\Request;

use \PHPUnit_Framework_TestCase;
use Vectorface\SnappyRouter\Request\HttpRequest;

/**
 * Tests the HttpRequest class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class HttpRequestTest extends PHPUnit_Framework_TestCase
{
    /**
     * An overview of how to use the RPCRequest class.
     * @test
     */
    public function synopsis()
    {
        // instantiate the class
        $request = new HttpRequest('MyService', 'MyMethod', 'GET', 'php://memory');

        $this->assertEquals('GET', $request->getVerb());
        $this->assertEquals('POST', $request->setVerb('POST')->getVerb());

        $queryData = array('id' => '1234');
        $this->assertTrue(
            1234 === $request->setQuery($queryData)->getQuery('id', 0, 'int')
        );

        $postData = array('username' => ' TEST_USER ');
        $this->assertEquals(
            'test_user',
            $request->setPost($postData)->getPost('username', '', array('trim', 'lower'))
        );

        $this->assertEquals(
            array('id' => '1234', 'username' => ' TEST_USER '),
            $request->getAllInput()
        );
    }

    /**
     * Tests successful input stream set and fetch cases
     */
    public function testInputStream()
    {
        $tempStream = fopen('php://memory', 'w');
        fwrite($tempStream, "test");
        rewind($tempStream);

        /* Mock a stream in memory */
        $request = new HttpRequest('TestService', 'TestMethod', 'GET', $tempStream);
        $this->assertEquals("test", $request->getBody());
        fclose($tempStream);

        /* Fetch previously stored value */
        $this->assertEquals("test", $request->getBody());

        /* Specify php://memory as a string */
        $request = new HttpRequest('TestService', 'TestMethod', 'GET', 'php://memory');
        $this->assertEquals("", $request->getBody());
    }

    /**
     * Tests the input stream functionality where the stream source is not a string or a stream
     * @expectedException Vectorface\SnappyRouter\Exception\InternalErrorException
     */
    public function testInputStreamIncorrectTypeFailure()
    {
        $request = new HttpRequest('TestService', 'TestMethod', 'GET', 1);
        $request->getBody();
    }

    /**
     * Tests the input stream functionality where the stream source does not exist
     * @expectedException Vectorface\SnappyRouter\Exception\InternalErrorException
     */
    public function testInputStreamIncorrectFileFailure()
    {
        $request = new HttpRequest('TestService', 'TestMethod', 'GET', 'file');
        $request->getBody();
    }

    /**
     * Tests the various filters.
     * @dataProvider filtersProvider
     */
    public function testInputFilters($expected, $value, $filters)
    {
        $request = new HttpRequest('TestService', 'TestMethod', 'GET', 'php://input');
        $request->setQuery(array('key' => $value));
        $this->assertTrue($expected === $request->getQuery('key', null, $filters));
    }

    /**
     * The data provider for testInputFilters.
     */
    public function filtersProvider()
    {
        return array(
            array(
                1234,
                '1234',
                'int'
            ),
            array(
                1234.5,
                ' 1234.5   ',
                'float'
            ),
            array(
                'hello world',
                "\t".'hello world  '.PHP_EOL,
                'trim'
            ),
            array(
                'hello world',
                'HELLO WORLD',
                'lower'
            ),
            array(
                'HELLO WORLD',
                'hello world',
                'upper'
            ),
            array(
                'test'.PHP_EOL.'string',
                'test'.PHP_EOL.'  '.PHP_EOL.PHP_EOL.'string',
                'squeeze'
            ),
        );
    }
}
