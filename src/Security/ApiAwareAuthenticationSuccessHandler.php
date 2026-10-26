<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Uri\Rfc3986\Uri;

final class ApiAwareAuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $firewallName = $this->getFirewallName() ?? 'main';
        $sessionKey = '_security.' . $firewallName . '.target_path';
        $session = $request->getSession();
        $targetPath = $session->get($sessionKey);

        // When login session expires while player is doing async /game/api requests on the worldmap,
        // Don't forward the player to a json /game/api endpoint. Logins should always go to the worldmap
        if (is_string($targetPath)) {
            $path = Uri::parse($targetPath)?->getPath();
            if ($path !== null && str_starts_with($path, '/game/api/')) {
                $session->remove($sessionKey);
            }
        }

        /** @var Response $response */
        $response = parent::onAuthenticationSuccess($request, $token);

        return $response;
    }
}
