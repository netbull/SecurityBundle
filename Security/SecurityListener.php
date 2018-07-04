<?php

namespace NetBull\SecurityBundle\Security;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

use NetBull\SecurityBundle\Managers\SecurityManager;
use NetBull\SecurityBundle\Exception\BannedException;
use NetBull\SecurityBundle\Exception\InvalidRouteException;
use NetBull\SecurityBundle\Exception\InvalidFingerprintException;

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
     * @var string
     */
    protected $unbannedRoute;

    /**
     * SecurityListener constructor.
     * @param SecurityManager $manager
     * @param RouterInterface $router
     * @param null|string $bannedRoute
     * @param null|string $unbannedRoute
     */
    public function __construct(SecurityManager $manager, RouterInterface $router, ?string $bannedRoute = null, ?string $unbannedRoute = null)
    {
        $this->manager = $manager;
        $this->router = $router;
        $this->bannedRoute = $bannedRoute;
        $this->unbannedRoute = $unbannedRoute;
    }

    /**
     * @param GetResponseEvent $event
     * @throws InvalidRouteException
     * @throws InvalidFingerprintException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$event->isMasterRequest()) {
            return;
        }

        $fingerprint = $this->manager->computeFingerprint($request);
        $isBlocked = $this->manager->isBlocked($fingerprint);

        if (null !== $this->bannedRoute && null !== $this->unbannedRoute && $request->get('_route') === $this->bannedRoute && !$isBlocked) {
            if ($this->router->getRouteCollection()->get($this->unbannedRoute)) {
                $response = $this->getRedirectResponse($this->unbannedRoute);
                $event->setResponse($response);
                return;
            } else {
                throw new InvalidRouteException($this->unbannedRoute);
            }
        }

        if ($request->get('_route') === $this->bannedRoute) {
            return;
        }

        if ($isBlocked) {
            if ($this->bannedRoute && $this->router->getRouteCollection()->get($this->bannedRoute)) {
                $response = $this->getRedirectResponse($this->bannedRoute);
                $event->setResponse($response);
            } else if (null !== $this->bannedRoute) {
                throw new InvalidRouteException($this->bannedRoute);
            } else {
                throw new BannedException();
            }
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
