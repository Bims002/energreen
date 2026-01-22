<?php

namespace App\Service\User;

use App\Entity\User;

interface UserRegistrationServiceInterface
{
    /**
     * Crée et enregistre un nouvel utilisateur
     * 
     * @param User $user
     * @param string $plainPassword
     * @return void
     */
    public function registerUser(User $user, string $plainPassword): void;
}
