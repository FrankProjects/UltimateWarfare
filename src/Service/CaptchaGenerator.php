<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Service;

use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;

final class CaptchaGenerator
{
    private const int IMAGE_WIDTH = 200;
    private const int IMAGE_HEIGHT = 70;
    private const string SESSION_KEY = '_captcha_answer';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function generateImage(): string
    {
        [$question, $answer] = $this->generateMathProblem();

        $session = $this->requestStack->getSession();
        $session->set(self::SESSION_KEY, $answer);

        return $this->renderImage($question);
    }

    public function validateAnswer(string $userInput): bool
    {
        $session = $this->requestStack->getSession();
        $storedAnswer = $session->get(self::SESSION_KEY);
        $session->remove(self::SESSION_KEY);

        if (!is_int($storedAnswer)) {
            return false;
        }

        return (int) $userInput === $storedAnswer;
    }

    /**
     * @return array{string, int}
     */
    private function generateMathProblem(): array
    {
        $operator = random_int(0, 1);

        if ($operator === 0) {
            $a = random_int(1, 20);
            $b = random_int(1, 20);
            $question = "{$a} + {$b} = ?";
            $answer = $a + $b;
        } else {
            $a = random_int(2, 20);
            $b = random_int(1, $a);
            $question = "{$a} - {$b} = ?";
            $answer = $a - $b;
        }

        return [$question, $answer];
    }

    private function renderImage(string $text): string
    {
        $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);
        if ($image === false) {
            throw new RuntimeException('Failed to create captcha image');
        }

        $bgColor = imagecolorallocate($image, random_int(220, 250), random_int(220, 250), random_int(220, 250));
        if ($bgColor === false) {
            throw new RuntimeException('Failed to allocate background color');
        }
        imagefilledrectangle($image, 0, 0, self::IMAGE_WIDTH - 1, self::IMAGE_HEIGHT - 1, $bgColor);

        $this->addNoise($image);

        $textColor = imagecolorallocate($image, random_int(0, 80), random_int(0, 80), random_int(0, 80));
        if ($textColor === false) {
            throw new RuntimeException('Failed to allocate text color');
        }

        $charWidth = imagefontwidth(5);
        $textWidth = strlen($text) * $charWidth;
        $x = (int) ((self::IMAGE_WIDTH - $textWidth) / 2);

        for ($i = 0; $i < strlen($text); $i++) {
            $y = random_int(20, 35);
            imagestring($image, 5, $x + ($i * $charWidth), $y, $text[$i], $textColor);
        }

        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);

        if ($data === false) {
            throw new RuntimeException('Failed to capture captcha image output');
        }

        return $data;
    }

    private function addNoise(\GdImage $image): void
    {
        $lineCount = random_int(5, 8);
        for ($i = 0; $i < $lineCount; $i++) {
            $lineColor = imagecolorallocate($image, random_int(100, 200), random_int(100, 200), random_int(100, 200));
            if ($lineColor === false) {
                continue;
            }
            imageline(
                $image,
                random_int(0, self::IMAGE_WIDTH),
                random_int(0, self::IMAGE_HEIGHT),
                random_int(0, self::IMAGE_WIDTH),
                random_int(0, self::IMAGE_HEIGHT),
                $lineColor
            );
        }

        $dotCount = random_int(50, 100);
        for ($i = 0; $i < $dotCount; $i++) {
            $dotColor = imagecolorallocate($image, random_int(100, 200), random_int(100, 200), random_int(100, 200));
            if ($dotColor === false) {
                continue;
            }
            imagesetpixel($image, random_int(0, self::IMAGE_WIDTH), random_int(0, self::IMAGE_HEIGHT), $dotColor);
        }
    }
}
