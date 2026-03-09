<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class LoginE2ETest extends PantherTestCase
{
    public function testLogin(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login');

        // On vérifie qu'on est sur la bonne page
        $this->assertSelectorTextContains('.welcome-title', 'Connectez-vous sur Energreen');

        // On remplit le formulaire
        $client->submitForm('Se connecter', [
            '_username' => 'test@energreen.com',
            '_password' => 'password123',
        ]);

        // On vérifie qu'on est redirigé vers le dashboard
        $client->waitFor('.dashboard-grid', 5); // Attend que l'élément apparaisse
        $this->assertSelectorTextContains('h2', 'Historique & Analyses');
    }
}
