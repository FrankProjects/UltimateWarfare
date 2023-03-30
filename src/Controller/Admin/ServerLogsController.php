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

        $logRegex = '/\[(?P<date>.*)\] (?P<channel>\w+).(?P<level>\w+): (?P<message>[^\[\{].*[\]\}])/';
        $logs = [];
        foreach ($finder as $file) {
            $logLines = file($file->getRealPath());
            foreach ($logLines as $logLine) {
                preg_match($logRegex, $logLine, $logData);
                $logs[$file->getFilename()][] = $logData;
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
