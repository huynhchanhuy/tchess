<?php

namespace Tchess\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

class ControllerListener implements EventSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container The service container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        list($object, $method) = $controller;

        if ($object instanceof ContainerAwareInterface) {
            $object->setContainer($this->container);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(array('onKernelController', 0)),
        );
    }

}
