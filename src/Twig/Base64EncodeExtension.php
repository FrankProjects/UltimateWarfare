<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Base64EncodeExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('base64_encode', [$this, 'base64Encode']),
        ];
    }

    public function base64Encode(mixed $stream): string
    {
        if (!is_resource($stream)) {
            return '';
        }

        return base64_encode((string) stream_get_contents($stream));
    }
}
