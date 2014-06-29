<?php

namespace Tchess\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Tchess\FrameworkInterface;

class ControllerListener implements EventSubscriberInterface
{

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        list($object, $method) = $controller;
        $kernel = $event->getKernel();

        if ($object instanceof ContainerAwareInterface && $kernel instanceof FrameworkInterface) {
            $object->setContainer($kernel->getContainer());
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(array('onKernelController', 0)),
        );
    }

}
