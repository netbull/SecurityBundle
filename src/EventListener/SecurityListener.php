<?php

namespace NetBull\SecurityBundle\EventListener;

use NetBull\SecurityBundle\Exception\BannedException;
use NetBull\SecurityBundle\Exception\InvalidFingerprintException;
use NetBull\SecurityBundle\Exception\InvalidRouteException;
use NetBull\SecurityBundle\Managers\SecurityManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class SecurityListener
{
    /**
     * @var SecurityManager
     */
    protected SecurityManager $manager;

    /**
     * @var RouterInterface
     */
    protected RouterInterface $router;

    /**
     * @var string|null
     */
    protected ?string $bannedRoute = null;

    /**
     * @var string|null
     */
    protected ?string $unbannedRoute = null;

    /**
     * @param SecurityManager $manager
     * @param RouterInterface $router
     * @param string|null $bannedRoute
     * @param string|null $unbannedRoute
     */
    public function __construct(SecurityManager $manager, RouterInterface $router, ?string $bannedRoute = null, ?string $unbannedRoute = null)
    {
        $this->manager = $manager;
        $this->router = $router;
        $this->bannedRoute = $bannedRoute;
        $this->unbannedRoute = $unbannedRoute;
    }

    /**
     * @param RequestEvent $event
     * @throws InvalidRouteException
     * @throws InvalidFingerprintException
     */
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        if (!$event->isMainRequest()) {
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
     * @return RedirectResponse
     */
    protected function getRedirectResponse(string $routeName): RedirectResponse
    {
        return new RedirectResponse($this->router->generate($routeName));
    }
}
