<?php

namespace Tchess\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Tchess\FrameworkInterface;
use Tchess\FrameworkAwareInterface;

class ControllerListener implements EventSubscriberInterface
{

    public function onKernelController(FilterControllerEvent $event)
    {
        $controller = $event->getController();
        list($object, $method) = $controller;
        $kernel = $event->getKernel();

        if ($object instanceof FrameworkAwareInterface && $kernel instanceof FrameworkInterface) {
            $object->setFramework($kernel);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::CONTROLLER => array(array('onKernelController', 0)),
        );
    }

}
