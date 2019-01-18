<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Game;

use FrankProjects\UltimateWarfare\Service\Action\FederationBankActionService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class FederationBankController extends BaseGameController
{
    /**
     * @var FederationBankActionService
     */
    private $federationBankActionService;

    /**
     * FederationBankController constructor.
     *
     * @param FederationBankActionService $federationBankActionService
     */
    public function __construct(
        FederationBankActionService $federationBankActionService
    ) {
        $this->federationBankActionService = $federationBankActionService;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function deposit(Request $request): Response
    {
        try {
            $resources = $request->get('resources');
            if ($request->isMethod(Request::METHOD_POST) && $resources !== null) {
                $this->federationBankActionService->deposit($this->getPlayer(), $resources);
                $this->addFlash('success', 'You successfully made a deposit!');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/federation/bank/deposit.html.twig', [
            'player' => $this->getPlayer(),
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function withdraw(Request $request): Response
    {
        try {
            $resources = $request->get('resources');
            if ($request->isMethod(Request::METHOD_POST) && $resources !== null) {
                $this->federationBankActionService->withdraw($this->getPlayer(), $resources);
                $this->addFlash('success', 'You successfully made a withdrawal!');
            }
        } catch (Throwable $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('game/federation/bank/withdraw.html.twig', [
            'player' => $this->getPlayer(),
        ]);
    }
}
