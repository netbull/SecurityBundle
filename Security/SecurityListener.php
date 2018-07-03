<?php

namespace NetBull\SecurityBundle\Security;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use NetBull\SecurityBundle\Managers\SecurityManager;

/**
 * Class SecurityListener
 * @package NetBull\SecurityBundle\Security
 */
class SecurityListener
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
     * SecurityListener constructor.
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
        $request = $event->getRequest();
        if (!$event->isMasterRequest() || $request->get('_route') === $this->bannedRoute) {
            return;
        }

        $fingerprint = $this->manager->computeFingerprint($request);

        if ($this->manager->isBlocked($fingerprint)) {
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
