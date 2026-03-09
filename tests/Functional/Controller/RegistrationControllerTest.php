<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testRegistrationPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Inscription ENERGREEN');
    }

    public function testSubmitRegistrationForm(): void
    {
        $client = static::createClient();

        // Nettoyage préalable de l'utilisateur de test s'il existe
        $em = static::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'newuser@energreen.com']);
        if ($user) {
            $em->remove($user);
            $em->flush();
        }

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Créer mon compte')->form([
            'registration_form[email]' => 'newuser@energreen.com',
            'registration_form[nom]' => 'New',
            'registration_form[prenom]' => 'User',
            'registration_form[statut_pro]' => 'Particulier',
            'registration_form[agreeTerms]' => true,
            'registration_form[plainPassword][first]' => 'password123',
            'registration_form[plainPassword][second]' => 'password123',
        ]);

        $client->submit($form);

        // Vérifie la redirection vers login
        $this->assertResponseRedirects('/login');
        $client->followRedirect();

        $this->assertSelectorTextContains('.alert-success', 'Votre compte a été créé avec succès');

        // Vérifie que l'utilisateur est bien en base
        $user = static::getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'newuser@energreen.com']);
        $this->assertNotNull($user);
        $this->assertEquals('New', $user->getNom());
    }
}
