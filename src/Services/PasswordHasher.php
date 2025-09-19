<?php

namespace App\Services;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * This service is used to hash passwords.
 */
class PasswordHasher
{
    // The password hasher service
    private UserPasswordHasherInterface $passwordHasher;

    // Constructor method to inject the password hasher service
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    /**
     * Hashes the password.
     */
    public function hashPassword(PasswordAuthenticatedUserInterface $user, string $plainPassword): string
    {
        // Hashes the password and returns it as a string.
        return $this->passwordHasher->hashPassword($user, $plainPassword);
    }
}