<?php

// src/Service/FirebaseService.php
namespace App\Service;

use Kreait\Firebase\Factory;

class FirebaseService
{
    private $firebase;

    public function __construct(string $firebaseConfigPath)
    {
        $this->firebase = (new Factory)
            ->withServiceAccount($firebaseConfigPath)
            ->create();
    }

    public function getAuth()
    {
        return $this->firebase->getAuth();
    }
}
