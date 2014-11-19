<?php

namespace Vectorface\SnappyRouter\Encoder;

use \Twig_Loader_Filesystem;
use \Twig_Environment;
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
     * @param array $viewConfig The view configuration.
     * @param string $template The name of the default template to render
     */
    public function __construct($viewConfig, $template)
    {
        if (!isset($viewConfig[ControllerHandler::KEY_VIEWS_PATH])) {
            throw new InternalErrorException(
                'View environment missing views path.'
            );
        }
        $loader = new Twig_Loader_Filesystem(
            $viewConfig[ControllerHandler::KEY_VIEWS_PATH]
        );
        $this->viewEnvironment = new Twig_Environment($loader, $viewConfig);
        $this->template = $template;
    }

    /**
     * Returns the Twig view environment.
     * @return Twig_Environment The configured twig environment.
     */
    public function getViewEnvironment()
    {
        return $this->viewEnvironment;
    }

    /**
     * @param AbstractResponse $response The response to be encoded.
     * @return string Returns the response encoded as a string.
     */
    public function encode(AbstractResponse $response)
    {
        $responseObject = $response->getResponseObject();
        if (is_string($responseObject)) {
            return $responseObject;
        } else {
            return $this->viewEnvironment->loadTemplate(
                $this->template
            )->render(
                (array)$responseObject
            );
        }
    }

    /**
     * Renders an abitrary view with arbitrary parameters.
     * @param string $template The template to render.
     * @param array $variables The variables to use.
     * @return string Returns the rendered template as a string.
     */
    public function renderView($template, $variables)
    {
        return $this->getViewEnvironment()->loadTemplate(
            $template
        )->render(
            (array)$variables
        );
    }
}
