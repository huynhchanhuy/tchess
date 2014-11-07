<?php

namespace Tchess\Controller;

use Tchess\FrameworkAware;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Tchess\Entity\Player;

class BaseController extends FrameworkAware
{

    /**
     * Render a template
     */
    public function isLoggedIn(Request $request)
    {
        $em = $this->framework->getEntityManager();
        $session = $request->getSession();
        $sid = $session->getId();
        $player = $em->getRepository('Tchess\Entity\Player')->findOneBy(array('sid' => $sid));

        if (empty($player) || !$player instanceof Player) {
            return false;
        }
        return true;
    }

    /**
     * Render a template
     */
    public function render($template, $variables = array())
    {
        $twig = $this->framework->getTwig();
        return $twig->render($template, $variables);
    }

    /**
     * Render a template
     */
    public function getFormFactory()
    {
        return $this->framework->getFormFactory();
    }

    /**
     * Generates a URL from the given parameters.
     *
     * @param string         $route         The name of the route
     * @param mixed          $parameters    An array of parameters
     * @param bool|string    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function generateUrl($route, $parameters = array(), $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        return $this->framework->getUrlGenerator()->generate($route, $parameters, $referenceType);
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param int     $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        return new RedirectResponse($url, $status);
    }

}
