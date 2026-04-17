<?php

namespace App\Service;

use App\Entity\BilanCarbone;
use App\Entity\ArchiveBilanCarbone;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class BilanCarboneManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Cette méthode archive le bilan actuel puis le supprime pour libérer la place
     */
    public function archiveOldBilan(BilanCarbone $bilan): void
    {
        $user = $bilan->getUtilisateur();

        // 1. Créer l'ARCHIVE
        $archive = new ArchiveBilanCarbone();
        $archive->setUser($user);
        $archive->setScoreTotal($bilan->getTotal() ?? 0.0);
        $archive->setCreatedAt(new \DateTimeImmutable());
        $archive->setDetails([
            'logement' => $bilan->getLogement(),
            'numerique' => $bilan->getNumerique(),
            'electromenager' => $bilan->getElectromenager(),
            'alimentation' => $bilan->getAlimentation(),
            'transports' => $bilan->getTransports(),
            'textile' => $bilan->getTextile(),
        ]);

        $this->em->persist($archive);

        // 2. Supprimer le bilan actuel (pour respecter l'unicité)
        $this->em->remove($bilan);

        // 3. Valider la transaction
        $this->em->flush();
    }
}