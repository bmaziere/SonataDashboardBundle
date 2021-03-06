<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\DashboardBundle\CmsManager;

use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

/**
 * This class returns the correct manager instance :
 *   - sonata.dashboard.cms.dashboard if the user is an editor (ROLE_SONATA_DASHBOARD_ADMIN_DASHBOARD_EDIT)
 *   - not found exception if the user is a standard user.
 *
 * @author Thomas Rabaix <thomas.rabaix@sonata-project.org>
 */
final class CmsManagerSelector implements CmsManagerSelectorInterface, LogoutHandlerInterface
{
    /**
     * @var CmsManagerInterface
     */
    private $cmsDashboardManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AdminInterface
     */
    private $admin;

    public function __construct(CmsManagerInterface $cmsDashboardManager, AdminInterface $admin, RequestStack $requestStack, SessionInterface $session, TokenStorageInterface $tokenStorage)
    {
        $this->cmsDashboardManager = $cmsDashboardManager;
        $this->admin = $admin;
        $this->requestStack = $requestStack;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        if ($this->tokenStorage->getToken() &&
            $this->admin->hasAccess('edit')) {
            $this->session->set('sonata/dashboard/isEditor', true);
        }
    }

    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $this->session->set('sonata/dashboard/isEditor', false);

        if ($request->cookies->has('sonata_dashboard_is_editor')) {
            $response->headers->clearCookie('sonata_dashboard_is_editor');
        }
    }

    public function retrieve(): CmsManagerInterface
    {
        return $this->cmsDashboardManager;
    }

    public function isEditor(): bool
    {
        /*
         * The current order of events is not suitable for the selector to be called
         * by the router chain, so we need to use another mechanism. It is not perfect
         * but does the job for now.
         */

        $request = $this->getRequest();
        $sessionAvailable = ($request && $request->hasPreviousSession()) || $this->session->isStarted();

        return $sessionAvailable && $this->session->get('sonata/dashboard/isEditor', false);
    }

    private function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }
}
