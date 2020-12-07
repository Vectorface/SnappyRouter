<?php

namespace Vectorface\SnappyRouter\Encoder;

use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Vectorface\SnappyRouter\Exception\InternalErrorException;
use Vectorface\SnappyRouter\Handler\ControllerHandler;
use Vectorface\SnappyRouter\Response\AbstractResponse;

/**
 * An encoder for rendering input through a Twig view.
 * @copyright Copyright (c) 2014, VectorFace, Inc.
 * @author Dan Bruce <dbruce@vectorface.com>
 */
class TwigViewEncoder extends AbstractEncoder
{
    // the template to encode
    private $template;

    // the twig view environment
    private $viewEnvironment;

    /**
     * Constructor for the encoder.
     *
     * @param array $viewConfig The view configuration.
     * @param string $template The name of the default template to render
     * @noinspection PhpMissingParentConstructorInspection
     * @throws InternalErrorException
     */
    public function __construct($viewConfig, $template)
    {
        if (!isset($viewConfig[ControllerHandler::KEY_VIEWS_PATH])) {
            throw new InternalErrorException(
                'View environment missing views path.'
            );
        }
        $loader = new FilesystemLoader($viewConfig[ControllerHandler::KEY_VIEWS_PATH]);
        $this->viewEnvironment = new Environment($loader, $viewConfig);
        $this->template = $template;
    }

    /**
     * Returns the Twig view environment.
     * @return Environment The configured twig environment.
     */
    public function getViewEnvironment()
    {
        return $this->viewEnvironment;
    }

    /**
     * @param AbstractResponse $response The response to be encoded.
     * @return string Returns the response encoded as a string.
     * @throws LoaderError|RuntimeError|SyntaxError
     */
    public function encode(AbstractResponse $response)
    {
        $responseObject = $response->getResponseObject();
        if (is_string($responseObject)) {
            return $responseObject;
        }

        return $this->viewEnvironment
            ->load($this->template)
            ->render((array)$responseObject);
    }

    /**
     * Renders an arbitrary view with arbitrary parameters.
     *
     * @param string $template The template to render.
     * @param array $variables The variables to use.
     * @return string Returns the rendered template as a string.
     * @throws LoaderError|RuntimeError|SyntaxError
     */
    public function renderView($template, $variables)
    {
        return $this->getViewEnvironment()
            ->load($template)
            ->render((array)$variables);
    }
}
