<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;

final class ServerLogsController extends AbstractController
{
    private const int MAX_ENTRIES_PER_FILE = 1000;
    private const int MAX_READ_BYTES = 2 * 1024 * 1024;

    public function showLogs(): Response
    {
        $finder = new Finder();
        $finder->in($this->getParameter('kernel.project_dir') . '/var/log');
        $finder->name('*.log');
        $finder->sortByName();

        $logs = [];
        $levels = [];
        foreach ($finder as $file) {
            $entries = [];
            foreach ($this->readLastLines($file->getPathname()) as $logLine) {
                $logData = json_decode($logLine, true);
                if (!is_array($logData)) {
                    continue;
                }

                $level = is_string($logData['level_name'] ?? null) ? $logData['level_name'] : 'UNKNOWN';
                $levelValue = is_int($logData['level'] ?? null) ? $logData['level'] : 0;
                $levels[$level] = $levelValue;

                $context = $logData['context'] ?? [];
                $contextJson = $context !== []
                    ? json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                    : false;
                $entries[] = [
                    'datetime' => is_string($logData['datetime'] ?? null) ? $logData['datetime'] : '',
                    'level' => $level,
                    'channel' => is_string($logData['channel'] ?? null) ? $logData['channel'] : '',
                    'message' => is_string($logData['message'] ?? null) ? $logData['message'] : '',
                    'context' => $contextJson !== false ? $contextJson : null,
                ];
            }

            $logs[$file->getFilename()] = array_reverse($entries);
        }

        asort($levels);

        return $this->render(
            'admin/serverLogs.html.twig',
            [
                'logs' => $logs,
                'levels' => array_keys($levels),
                'maxEntries' => self::MAX_ENTRIES_PER_FILE,
            ]
        );
    }

    /**
     * Reads only the tail of the file, log files can grow very large.
     *
     * @return array<int, string>
     */
    private function readLastLines(string $path): array
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $size = filesize($path);
        $offset = $size !== false ? max(0, $size - self::MAX_READ_BYTES) : 0;
        fseek($handle, $offset);
        $content = stream_get_contents($handle);
        fclose($handle);

        if ($content === false) {
            return [];
        }

        $lines = explode("\n", trim($content));
        if ($offset > 0) {
            // First line is most likely cut off
            array_shift($lines);
        }

        return array_slice($lines, -self::MAX_ENTRIES_PER_FILE);
    }
}
