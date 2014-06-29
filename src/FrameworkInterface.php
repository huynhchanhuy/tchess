<?php

namespace Tchess;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

interface FrameworkInterface extends HttpKernelInterface
{

    /**
     * Gets the current container.
     *
     * @return ContainerInterface A ContainerInterface instance
     *
     * @api
     */
    public function getContainer();
}
