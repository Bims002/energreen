<?php

namespace App\Service;

use App\Entity\BilanCarbone;
use App\Entity\ArchiveBilanCarbone; // On importe bien l'entité archive
use Doctrine\ORM\EntityManagerInterface;

class BilanCarboneManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Archive les données de BilanCarbone vers ArchiveBilanCarbone puis supprime le bilan actuel
     */
    public function archiveAndDeleteOldBilan(BilanCarbone $bilan): void
    {
        $archive = new ArchiveBilanCarbone();

        $archive->setUser($bilan->getUtilisateur());
        $archive->setScoreTotal($bilan->getTotal() ?? 0.0);

        // Vérifiez bien que ces getters existent dans BilanCarbone.php
        $archive->setDetails([
            'logement' => $bilan->getLogement() ?? 0.0,
            'numerique' => $bilan->getNumerique() ?? 0.0,
            'electromenager' => $bilan->getElectromenager() ?? 0.0,
            'alimentation' => $bilan->getAlimentation() ?? 0.0,
            'transports' => $bilan->getTransports() ?? 0.0,
            'textile' => $bilan->getTextile() ?? 0.0,
        ]);

        $this->em->persist($archive);
        $this->em->remove($bilan);
        // Le flush sera fait dans le contrôleur pour tout valider d'un coup
    }
}