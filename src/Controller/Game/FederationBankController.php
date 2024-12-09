<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Service\Action\FederationBankActionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class FederationBankController extends BaseGameController
{
    private FederationBankActionService $federationBankActionService;

    public function __construct(
        FederationBankActionService $federationBankActionService
    ) {
        $this->federationBankActionService = $federationBankActionService;
    }

    public function deposit(Request $request): Response
    {
        try {
            // XXX TODO: Rewrite to form isSubmitted && isValid
            if ($request->isMethod(Request::METHOD_POST)) {
                /** @var array<string, string> $resources */
                $resources = $request->get('resources');
                $this->federationBankActionService->deposit($this->getPlayer(), $resources);
                $this->addFlash('success', 'You successfully made a deposit!');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/bank/deposit.html.twig',
            [
                'player' => $this->getPlayer(),
            ]
        );
    }

    public function withdraw(Request $request): Response
    {
        try {
            // XXX TODO: Rewrite to form isSubmitted && isValid
            if ($request->isMethod(Request::METHOD_POST)) {
                /** @var array<string, string> $resources */
                $resources = $request->get('resources');
                $this->federationBankActionService->withdraw($this->getPlayer(), $resources);
                $this->addFlash('success', 'You successfully made a withdrawal!');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render(
            'game/federation/bank/withdraw.html.twig',
            [
                'player' => $this->getPlayer(),
            ]
        );
    }
}
