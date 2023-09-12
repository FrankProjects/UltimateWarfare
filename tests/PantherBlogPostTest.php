<?php

namespace FrankProjects\UltimateWarfare\Tests;

use Symfony\Component\Panther\PantherTestCase;

class PantherBlogPostTest extends PantherTestCase
{
    public function testSomething(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/');

        $this->assertSelectorTextContains('h1', 'Welcome to Ultimate Warfare');
    }
}
