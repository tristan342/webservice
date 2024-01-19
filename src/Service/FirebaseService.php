<?php

// src/Service/FirebaseService.php
namespace App\Service;

use Kreait\Firebase\Factory;

class FirebaseService
{
    private $firebase;
    private $auth;

    public function __construct(string $firebaseConfigPath)
    {

        $this->auth = (new Factory)
            ->withServiceAccount($firebaseConfigPath)
            ->createAuth();
        //$this->auth = $this->firebase->getAuth();
    }

    public function getAuth()
    {
        //phpinfo(); die();

        return $this->auth;
    }
}
