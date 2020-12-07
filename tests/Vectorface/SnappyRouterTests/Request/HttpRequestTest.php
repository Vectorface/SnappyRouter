<?php

namespace Vectorface\SnappyRouterTests\Request;

use PHPUnit\Framework\TestCase;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Request\HttpRequest;

/**
 * Tests the HttpRequest class.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class HttpRequestTest extends TestCase
{
    /**
     * An overview of how to use the RPCRequest class.
     */
    public function testSynopsis()
    {
        // instantiate the class
        $request = new HttpRequest('MyService', 'MyMethod', 'GET', 'php://memory');

        $this->assertEquals('GET', $request->getVerb());
        $this->assertEquals('POST', $request->setVerb('POST')->getVerb());

        $queryData = ['id' => '1234'];
        $this->assertSame(
            1234,
            $request->setQuery($queryData)->getQuery('id', 0, 'int')
        );

        $postData = ['username' => ' TEST_USER '];
        $this->assertEquals(
            'test_user',
            $request->setPost($postData)->getPost('username', '', ['trim', 'lower'])
        );

        $this->assertEquals(
            ['id' => '1234', 'username' => ' TEST_USER '],
            $request->getAllInput()
        );
    }

    /**
     * Tests successful input stream set and fetch cases
     *
     * @throws InternalErrorException
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
     *
     * @throws InternalErrorException
     */
    public function testInputStreamIncorrectTypeFailure()
    {
        $this->setExpectedException(InternalErrorException::class);

        $request = new HttpRequest('TestService', 'TestMethod', 'GET', 1);
        $request->getBody();
    }

    /**
     * Tests the input stream functionality where the stream source does not exist
     *
     * @throws InternalErrorException
     */
    public function testInputStreamIncorrectFileFailure()
    {
        $this->setExpectedException(InternalErrorException::class);

        $request = new HttpRequest('TestService', 'TestMethod', 'GET', 'file');
        $request->getBody();
    }

    /**
     * Tests the various filters.
     *
     * @dataProvider filtersProvider
     * @param string $expected
     * @param string $value
     * @param string $filters
     */
    public function testInputFilters($expected, $value, $filters)
    {
        $request = new HttpRequest('TestService', 'TestMethod', 'GET', 'php://input');
        $request->setQuery(['key' => $value]);
        $this->assertSame($expected, $request->getQuery('key', null, $filters));
    }

    /**
     * The data provider for testInputFilters.
     */
    public function filtersProvider()
    {
        return [
            [
                1234,
                '1234',
                'int'
            ],
            [
                1234.5,
                ' 1234.5   ',
                'float'
            ],
            [
                'hello world',
                "\t".'hello world  '.PHP_EOL,
                'trim'
            ],
            [
                'hello world',
                'HELLO WORLD',
                'lower'
            ],
            [
                'HELLO WORLD',
                'hello world',
                'upper'
            ],
            [
                'test'.PHP_EOL.'string',
                'test'.PHP_EOL.'  '.PHP_EOL.PHP_EOL.'string',
                'squeeze'
            ],
        ];
    }
}
