<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

final class ApiAwareAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if (str_starts_with($request->getPathInfo(), '/game/api/')) {
            return new JsonResponse(
                ['success' => false, 'message' => 'Session expired. Please log in again.'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        return new RedirectResponse('/login');
    }
}
