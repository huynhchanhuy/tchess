<?php

namespace Tchess\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

/**
 * Sets the session in the request.
 */
class SessionListener implements EventSubscriberInterface
{
    protected $storage;

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Prepare session.
        if (!$request->getSession()) {
            if (empty($this->storage)) {
                $this->storage = new NativeSessionStorage();
            }
            $session = new Session($this->storage);
            $session->getMetadataBag()->stampNew(0);
            $session->start();
            $request->setSession($session);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 128),
        );
    }

    public function setStorage(SessionStorageInterface $storage)
    {
        $this->storage = $storage;
    }
}
