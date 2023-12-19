<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
//use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class CreateAdminUserCommand extends Command
{
    protected static $defaultName = 'app:create-admin';

    private $entityManager;
    private $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure()
    {
        $this
            ->setDescription('Cette commande vous permet de créer un nouvel utilisateur avec le rôle ADMIN.')
            ->setHelp('Cette commande vous permet de créer un nouvel utilisateur avec le rôle ADMIN.')
            ->addArgument('email', InputArgument::REQUIRED, 'L\'email de l\'admin')
            ->addArgument('password', InputArgument::REQUIRED, 'Le mot de passe de l\'admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        // On récupère le rôle admin
        $roleAdmin = $this->entityManager->getRepository(Role::class)->findOneBy(['label' => 'ADMIN']);

        // Assurez-vous que $roleAdmin est bien un objet Role et non null
        if (!$roleAdmin) {
            $output->writeln('Role ADMIN non trouvé.');
            return Command::FAILURE;
        }
        // Création de l'instance de l'utilisateur.
        $user = new User();
        $user->setEmail($email);
        $user->setRole($roleAdmin);

        // Hashage du mot de passe.
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        // Enregistrement de l'utilisateur dans la base de données.
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Affichage du message de succès.
        $output->writeln('Admin créé avec succès !');

        // Retourne le code de succès de la commande.
        return Command::SUCCESS;
    }

}
