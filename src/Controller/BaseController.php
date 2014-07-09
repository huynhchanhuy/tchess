<?php

namespace Tchess\Controller;

use Symfony\Component\DependencyInjection\ContainerAware;

class BaseController extends ContainerAware
{

    /**
     * Render a template
     */
    public function render($template, $variables)
    {
        $twig = $this->container->get('twig');
        $twig->render($template, $variables);
    }

    /**
     * Render a template
     */
    public function getFormFactory()
    {
        return $this->container->get('form_factory');
    }

}
