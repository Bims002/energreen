<?php

namespace App\Service;

use App\Entity\BilanCarbone;
use App\Entity\ArchiveBilanCarbone;
use Doctrine\ORM\EntityManagerInterface;

class BilanCarboneManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function archiveAndDeleteOldBilan(BilanCarbone $bilan): void
    {
        $user = $bilan->getUtilisateur();
        $archive = new ArchiveBilanCarbone();

        $archive->setUser($user);
        $archive->setScoreTotal($bilan->getTotal() ?? 0.0);
        $archive->setDetails([
            'logement' => $bilan->getLogement() ?? 0.0,
            'numerique' => $bilan->getNumerique() ?? 0.0,
            'electromenager' => $bilan->getElectromenager() ?? 0.0,
            'alimentation' => $bilan->getAlimentation() ?? 0.0,
            'transports' => $bilan->getTransports() ?? 0.0,
            'textile' => $bilan->getTextile() ?? 0.0,
        ]);

        $this->em->persist($archive);

        // NE PAS FAIRE : $bilan->setUtilisateur(null); <--- C'est ça qui causait l'erreur
        // FAIRE : On demande la suppression directe
        $this->em->remove($bilan);

        // On ne flushe pas ici, on laisse le contrôleur le faire
    }
}