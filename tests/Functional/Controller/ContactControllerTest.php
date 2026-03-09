<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase
{
    public function testContactPageIsSuccessful(): void
    {
        $client = static::createClient();
        $client->request('GET', '/contact');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Contactez-nous');
    }

    public function testSubmitContactForm(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/contact');

        $buttonCrawlerNode = $crawler->selectButton('Envoyer le message');
        $form = $buttonCrawlerNode->form([
            'contact_form[nom]' => 'John Doe',
            'contact_form[email]' => 'john@example.com',
            'contact_form[subject]' => 'Sujet de test',
            'contact_form[message]' => 'Ceci est un message de test envoyé par un bot de test.',
        ]);

        $client->submit($form);

        // Vérifie l'envoi d'email (AVANT la redirection pour être sûr de la capture)
        $this->assertEmailCount(1);

        // Vérifie la redirection
        $this->assertResponseRedirects('/contact');
        $client->followRedirect();

        // Vérifie le message de succès
        $this->assertSelectorExists('.alert-success');
        $this->assertSelectorTextContains('.alert-success', 'Merci ! Votre message a été envoyé.');
    }
}
