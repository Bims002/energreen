<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Entity\ArchiveBilanCarbone;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormulaireCO2ControllerTest extends WebTestCase
{
    public function testCarbonFormSubmitAndArchive(): void
    {
        $client = static::createClient();
        $em = $client->getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(User::class)->findOneBy(['email' => 'test@energreen.com']);
        $client->loginUser($user);

        $client->request('POST', '/formulaire', [
            'surface' => 100,
            'isolation_etat' => 1.0,
            'energie_principale' => 0.052,
            'qty_smartphone' => 1,
            'heures_streaming' => 5,
            'km_voiture' => 5000,
            'vehicule_moteur' => 0.15,
            'regime_alimentaire' => 1800,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.toast-message', 'Votre bilan carbone a été mis à jour');

        // Vérifie qu'une archive a été créée
        $archive = $em->getRepository(ArchiveBilanCarbone::class)->findOneBy(['user' => $user], ['createdAt' => 'DESC']);
        $this->assertNotNull($archive);
        $this->assertGreaterThan(0, $archive->getScoreTotal());
    }
}
