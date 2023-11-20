<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CheckActiveUserListener
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');
        $pathInfo = $request->getPathInfo();

        $excludedRoutes = ['app_login'];

        if (!in_array($routeName, $excludedRoutes)) {
            /** @var \App\Entity\Participant $user */
            $user = $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : null;

            if ($user && method_exists($user, 'isActif') && !$user->isActif()) {
                throw new AccessDeniedException("Vous êtes banni. Contactez l'administrateur pour plus d'informations.");
            }

            // Vérifier si l'URL commence par /admin et que l'utilisateur n'est pas administrateur
            if (strpos($pathInfo, '/admin') === 0 && $user && method_exists($user, 'isAdministrateur') && !$user->isAdministrateur()) {
                throw new AccessDeniedException("Accès interdit. Vous n'avez pas les permissions nécessaires.");
            }
        }
    }
}
