<?php

namespace Tchess;

use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Tchess\FrameworkInterface;

class Framework extends HttpKernel implements ContainerAwareInterface, FrameworkInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Container aware.
     *
     * @param \ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Just like how Symfony\Component\HttpKernel\Kernel class works.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

}
