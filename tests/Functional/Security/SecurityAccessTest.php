<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityAccessTest extends WebTestCase
{
    /**
     * @dataProvider privateRoutesProvider
     */
    public function testPrivateRoutesRedirectToLogin(string $url): void
    {
        $client = static::createClient();
        $client->request('GET', $url);

        $this->assertResponseRedirects('/login');
    }

    public function privateRoutesProvider(): array
    {
        return [
            ['/dashboard'],
            ['/profil'],
            ['/profil/edit'],
            ['/calculator_consumption'],
            ['/formulaire'],
        ];
    }
}
