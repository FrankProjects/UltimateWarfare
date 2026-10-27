<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Asset;

use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Versions asset URLs with the file's modification time (css/main.css?v=1760561077), so browsers fetch a file
 * again as soon as it changes instead of running a stale cached copy.
 */
final class FileModificationVersionStrategy implements VersionStrategyInterface
{
    private string $publicDir;

    public function __construct(#[Autowire('%kernel.project_dir%/public')] string $publicDir)
    {
        $this->publicDir = $publicDir;
    }

    public function getVersion(string $path): string
    {
        $file = $this->publicDir . '/' . ltrim($path, '/');
        $modificationTime = is_file($file) ? filemtime($file) : false;

        return $modificationTime === false ? '' : (string) $modificationTime;
    }

    public function applyVersion(string $path): string
    {
        $version = $this->getVersion($path);

        return $version === '' ? $path : $path . '?v=' . $version;
    }
}
