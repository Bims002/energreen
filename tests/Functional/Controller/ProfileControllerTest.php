<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    private function getTestUser($client): User
    {
        return $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'test@energreen.com']);
    }

    public function testProfilePageIsSuccessful(): void
    {
        $client = static::createClient();
        $user = $this->getTestUser($client);
        $client->loginUser($user);

        $client->request('GET', '/profil');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Mon Profil');
    }

    public function testProfileEditPageIsSuccessful(): void
    {
        $client = static::createClient();
        $user = $this->getTestUser($client);
        $client->loginUser($user);

        $client->request('GET', '/profil/edit');

        $this->assertResponseIsSuccessful();
    }

    public function testUpdateProfile(): void
    {
        $client = static::createClient();
        $user = $this->getTestUser($client);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/profil/edit');

        // On récupère le token CSRF spécifique au formulaire de mise à jour
        $token = $crawler->filter('form[action="/profil/update"] input[name="_csrf_token"]')->attr('value');

        // On simule manuellement car le formulaire est peut-être complexe (JS)
        $client->request('POST', '/profil/update', [
            '_csrf_token' => $token,
            'nom' => 'UpdatedName',
            'prenom' => 'UpdatedPrenom',
            'email' => 'test@energreen.com',
            'statut_pro' => 'Entreprise',
            'lodgment_type' => 'Appartement',
            'surface' => 80,
            'occupant' => 3
        ]);

        $this->assertResponseRedirects('/profil/edit');
        $client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'Profil et logement mis à jour avec succès');

        // Vérifie en DB
        $user = $this->getTestUser($client);
        $this->assertEquals('UpdatedName', $user->getNom());
    }
}
