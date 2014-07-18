<?php

namespace Tchess;

abstract class FrameworkAware implements FrameworkAwareInterface
{
    /**
     * @var FrameworkInterface
     *
     * @api
     */
    protected $framework;

    /**
     * Sets the Framework associated with this Controller.
     *
     * @param FrameworkInterface $container A FrameworkInterface instance
     *
     * @api
     */
    public function setFramework(FrameworkInterface $framework = null)
    {
        $this->framework = $framework;
    }
}
