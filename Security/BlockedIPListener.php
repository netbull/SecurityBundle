<?php

namespace NetBull\SecurityBundle\Security;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use NetBull\SecurityBundle\Entity\ListedIP;
use NetBull\SecurityBundle\Managers\SecurityManager;

/**
 * Class BlockedIPListener
 * @package NetBull\SecurityBundle\Security
 */
class BlockedIPListener
{
    /**
     * @var SecurityManager
     */
    protected $manager;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var string
     */
    protected $bannedRoute;

    /**
     * BlockedIPListener constructor.
     * @param SecurityManager $manager
     * @param RouterInterface $router
     * @param string $bannedRoute
     */
    public function __construct(SecurityManager $manager, RouterInterface $router, string $bannedRoute)
    {
        $this->router = $router;
        $this->bannedRoute = $bannedRoute;
        $this->manager = $manager;
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return RedirectResponse|void
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $ip = $event->getRequest()->getClientIp();

        $listedRecord = $this->manager->isIPListed($ip);
        if ($listedRecord) {
            switch ($listedRecord['action']) {
                case ListedIP::ACTION_ALLOW:
                    return;
                case ListedIP::ACTION_DENY:
                    $response = $this->getRedirectResponse($this->bannedRoute);
                    $event->setResponse($response);
                    break;
            }
        } elseif ($this->manager->isIPBlocked($ip)) {
            $response = $this->getRedirectResponse($this->bannedRoute);
            $event->setResponse($response);
        }
    }

    /**
     * @param string $routeName
     *
     * @return RedirectResponse
     */
    protected function getRedirectResponse($routeName)
    {
        return new RedirectResponse(
            $this->router->generate($routeName)
        );
    }
}
