<?php

namespace App\Tests\Functional\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalculatorConsumptionControllerTest extends WebTestCase
{
    public function testIndexRedirectsToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/calculator_consumption');

        $this->assertResponseRedirects('/login');
    }

    public function testIndexIsAccessibleWhenAuthenticated(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);
        $testUser = $userRepository->findOneByEmail('test@energreen.com');

        $client->loginUser($testUser);
        $client->request('GET', '/calculator_consumption');

        $this->assertResponseIsSuccessful();
    }
}
