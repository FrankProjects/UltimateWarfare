<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

final class ServerLogsController extends AbstractController
{
    public function showLogs(): Response
    {
        $finder = new Finder();
        $finder->in($this->getParameter('kernel.project_dir') . '/var/log');
        $finder->name('*.log');
        $finder->sortByName();

        $logs = [];
        foreach ($finder as $file) {
            $logLines = file($file->getRealPath());
            if ($logLines === false) {
                continue;
            }
            foreach ($logLines as $logLine) {
                $logData = json_decode($logLine, true);
                if ($logData !== null) {
                    $logs[$file->getFilename()][] = $logData;
                }
            }
        }

        return $this->render(
            'admin/serverLogs.html.twig',
            [
                'logs' => $logs
            ]
        );
    }
}
