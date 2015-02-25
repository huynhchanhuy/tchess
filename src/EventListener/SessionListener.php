<?php

namespace Tchess\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Sets the session in the request.
 */
class SessionListener implements EventSubscriberInterface
{
    protected $session;

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        // Prepare session.
        if (!$request->getSession()) {
            $this->session->getMetadataBag()->stampNew(0);
            $this->session->start();
            $request->setSession($this->session);
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array('onKernelRequest', 128),
        );
    }

    public function setSession(SessionInterface $session)
    {
        $this->session = $session;
    }
}
