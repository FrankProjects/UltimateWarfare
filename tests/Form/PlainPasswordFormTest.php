<?php

declare(strict_types=1);

namespace FrankProjects\UltimateWarfare\Tests\Form;

use FrankProjects\UltimateWarfare\Entity\User;
use FrankProjects\UltimateWarfare\Form\CaptchaType;
use FrankProjects\UltimateWarfare\Form\RegistrationType;
use FrankProjects\UltimateWarfare\Form\ResetPasswordType;
use FrankProjects\UltimateWarfare\Service\TurnstileVerifier;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\NullLogger;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;

/**
 * The password fields are not mapped onto User; controllers read them from the form once it is valid.
 * Length skips empty values, so NotBlank is what keeps a blank or missing password from passing validation.
 */
final class PlainPasswordFormTest extends TypeTestCase
{
    /**
     * @return array<\Symfony\Component\Form\FormExtensionInterface>
     */
    protected function getExtensions(): array
    {
        $captchaType = new CaptchaType(
            new TurnstileVerifier(new MockHttpClient(), new RequestStack(), new NullLogger(), 'secret'),
            'site-key'
        );

        return [
            new PreloadedExtension([$captchaType], []),
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function emptyPasswordProvider(): array
    {
        return [
            'both fields empty' => [['first' => '', 'second' => '']],
            'fields missing' => [[]],
        ];
    }

    /**
     * @param array<string, mixed> $plainPassword
     */
    #[DataProvider('emptyPasswordProvider')]
    public function testRegistrationWithEmptyPasswordIsInvalid(array $plainPassword): void
    {
        $form = $this->factory->create(RegistrationType::class, new User());

        $form->submit(['email' => 'test@example.com', 'username' => 'tester', 'plainPassword' => $plainPassword]);

        self::assertFalse($form->get('plainPassword')->isValid());
    }

    public function testRegistrationPasswordIsReadableFromForm(): void
    {
        $form = $this->factory->create(RegistrationType::class, new User());

        $form->submit([
            'email' => 'test@example.com',
            'username' => 'tester',
            'plainPassword' => ['first' => 'secret-password', 'second' => 'secret-password'],
        ]);

        self::assertTrue($form->get('plainPassword')->isValid());
        self::assertSame('secret-password', $form->get('plainPassword')->getData());
    }

    /**
     * @param array<string, mixed> $plainPassword
     */
    #[DataProvider('emptyPasswordProvider')]
    public function testResetPasswordWithEmptyPasswordIsInvalid(array $plainPassword): void
    {
        $form = $this->factory->create(ResetPasswordType::class);

        $form->submit(['plainPassword' => $plainPassword]);

        self::assertFalse($form->isValid());
    }

    public function testResetPasswordWithMatchingPasswordIsValid(): void
    {
        $form = $this->factory->create(ResetPasswordType::class);

        $form->submit(['plainPassword' => ['first' => 'secret-password', 'second' => 'secret-password']]);

        self::assertTrue($form->isValid());
        self::assertSame('secret-password', $form->get('plainPassword')->getData());
    }
}
