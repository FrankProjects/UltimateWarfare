<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TurnstileVerifier
{
    private const string SITEVERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly RequestStack $requestStack,
        private readonly LoggerInterface $logger,
        private readonly string $secretKey
    ) {
    }

    public function verify(string $token, string $expectedAction): bool
    {
        if ($this->secretKey === '') {
            $this->logger->error('Turnstile secret key is not configured');
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return false;
        }

        $body = [
            'secret' => $this->secretKey,
            'response' => $token,
        ];

        $clientIp = $request->getClientIp();
        if ($clientIp !== null) {
            $body['remoteip'] = $clientIp;
        }

        try {
            $result = $this->httpClient->request('POST', self::SITEVERIFY_URL, [
                'body' => $body,
                'timeout' => 10,
            ])->toArray();
        } catch (ExceptionInterface $e) {
            $this->logger->error('Turnstile siteverify request failed', ['exception' => $e]);
            return false;
        }

        if (($result['success'] ?? false) !== true) {
            $this->logger->info('Turnstile verification failed', ['error-codes' => $result['error-codes'] ?? []]);
            return false;
        }

        if (($result['action'] ?? null) !== $expectedAction) {
            $this->logger->warning('Turnstile action mismatch', ['action' => $result['action'] ?? null]);
            return false;
        }

        if (($result['hostname'] ?? null) !== $request->getHost()) {
            $this->logger->warning('Turnstile hostname mismatch', ['hostname' => $result['hostname'] ?? null]);
            return false;
        }

        return true;
    }
}
