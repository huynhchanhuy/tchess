<?php

namespace Tchess\Tests;

use Tchess\Framework;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\EventListener\RouterListener;

class FrameworkTestDeprecated extends \PHPUnit_Framework_TestCase
{

    public function testNotFoundHandling()
    {
        $framework = $this->getFrameworkForException(new ResourceNotFoundException());

        $response = $framework->handle(new Request());

        $this->assertEquals(404, $response->getStatusCode());
    }

    protected function getFrameworkForException($exception)
    {
        $matcher = $this->getMock('Symfony\Component\Routing\Matcher\UrlMatcherInterface');
        $matcher
                ->expects($this->once())
                ->method('match')
                ->will($this->throwException($exception))
        ;
        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber(new RouterListener($matcher));
        $resolver = $this->getMock('Symfony\Component\HttpKernel\Controller\ControllerResolverInterface');

        return new Framework($dispatcher, $resolver);
    }

}