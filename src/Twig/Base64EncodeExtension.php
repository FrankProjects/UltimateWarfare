<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Base64EncodeExtension extends AbstractExtension
{
    public const GET_CONTENT_FROM_STREAM = 'stream';

    public function getFilters(): array
    {
        return [
            new TwigFilter('base64_encode', [$this, 'base64Encode']),
        ];
    }

    public function base64Encode(mixed $string, string $modifier = ''): string
    {
        $content = match ($modifier) {
            self::GET_CONTENT_FROM_STREAM => stream_get_contents($string),
            default => (string) $string,
        };
        return base64_encode((string) $content);
    }
}
