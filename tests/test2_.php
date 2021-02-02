<?php

/** @noinspection PhpUnhandledExceptionInspection */

use Doctrine\ORM\EntityManager;
use Teamsoft\EntityCopy\EntityCopy;
use Teamsoft\EntityCopy\Tests\Entity\UserEntity;
use Teamsoft\EntityCopy\Tests\Entity\EmailEntity;
use Teamsoft\EntityCopy\Tests\Entity\UserWalletEntity;
use Teamsoft\EntityCopy\Tests\Entity\UserProfileEntity;

/**
 * @var EntityCopy    $cloner
 * @var EntityManager $entityManager
 */

$entityManager = require_once __DIR__ . '/bootstrap/doctrine.php';
$cloner = require __DIR__ . '/bootstrap/doctrineCloner.php';


$fnTruncate = function () use ($entityManager) {
    $databaseConnection = $entityManager->getConnection();
    $databasePlatform = $databaseConnection->getDatabasePlatform();

    $databaseConnection->executeQuery('SET FOREIGN_KEY_CHECKS = 0;');
    // many-to-many
    $databaseConnection->executeStatement($sql = $databasePlatform->getTruncateTableSQL('users_emails'));
    // one-to-many
    $databaseConnection->executeStatement($sql = $databasePlatform->getTruncateTableSQL('user_wallets'));
    // one-to-one
    $databaseConnection->executeStatement($sql = $databasePlatform->getTruncateTableSQL('user_profiles'));
    // models
    $databaseConnection->executeStatement($sql = $databasePlatform->getTruncateTableSQL('users'));
    $databaseConnection->executeStatement($sql = $databasePlatform->getTruncateTableSQL('emails'));
    $databaseConnection->executeQuery('SET FOREIGN_KEY_CHECKS = 1;');
};

$fnCreate = function () use ($entityManager) {
    // new user
    $user = new UserEntity();
    $user->login = 'user';
    $entityManager->persist($user);


    // ...with profile: @one-to-one
    $userProfile = new UserProfileEntity();
    $userProfile->registered_at = new \DateTime();
    $userProfile->user = $user;
    $entityManager->persist($userProfile);

    # $user->userProfile = $userProfile;


    // ...with 2 wallets: @one-to-many
    $userWallet1 = new UserWalletEntity();
    $userWallet1->currency = 'usd';
    $userWallet1->value_calculated = 0;
    $userWallet1->calculated_at = new \DateTime();
    $userWallet1->user = $user;
    $entityManager->persist($userWallet1);

    $userWallet2 = new UserWalletEntity();
    $userWallet2->currency = 'eur';
    $userWallet2->value_calculated = 0;
    $userWallet2->calculated_at = new \DateTime();
    $userWallet2->user = $user;
    $entityManager->persist($userWallet2);

    # $user->userWallets->add($userWallet1);
    # $user->userWallets->add($userWallet2);


    // with 2 emails: @many-to-many
    $email1 = new EmailEntity();
    $email1->email = 'a@a.com';
    $email1->emailUsers->add($user);
    // $user->userEmails->add($email1);
    $entityManager->persist($email1);

    $email2 = new EmailEntity();
    $email2->email = 'b@b.com';
    $email2->emailUsers->add($user);
    // $user->userEmails->add($email2);
    $entityManager->persist($email2);

    # $user->userEmails->add($userEmail1);
    # $user->userEmails->add($userEmail2);


    $entityManager->flush();
    $entityManager->refresh($user);

    return $user;
};

$fnSelect = function () use ($entityManager) {
    $sql = [];
    $sql[] = 'SELECT u';
    $sql[] = 'FROM ' . UserEntity::class . ' u';
    $sql = implode(' ', $sql);

    $query = $entityManager->createQuery($sql);
    $query->setMaxResults(1);

    $result = $query->getResult();

    $users = $result;
    $user = $users[ 0 ];

    return $user;
};

$fnClone = function (UserEntity $user) use ($entityManager) {
    $userClone = clone $user;

    $entityManager->persist($userClone);

    return $userClone;
};

$fnCopy = function (UserEntity $user) use ($cloner, $entityManager) {
    $builder = $cloner
        ->from($user)
        ->one('userProfile', UserProfileEntity::class)
        ->many('userWallets', UserWalletEntity::class)
        ->many('userEmails', EmailEntity::class)//
    ;

    $userCopy = $builder->build();

    $entityManager->persist($userCopy);

    return $userCopy;
};


// 1. TRUNCATE
$fnTruncate();

// 2. NEW
$user = $fnCreate();
dump([
    $user->id,
    [ $user->userProfile, ! ! $user->userProfile ],
    [ $user->userWallets, count($user->userWallets) ],
    [ $user->userEmails, count($user->userEmails) ],
]);

// 3. SELECT
// $userSelected = $fnSelect();
// dump($user->id, $user->userEmails, count($user->userEmails));

// 4. COPY
// $userClone = null;
// $userClone = $fnClone($user);

$userCopy = null;
$userCopy = $fnCopy($user);

$entityManager->flush();
// $entityManager->refresh($userClone);
$entityManager->refresh($userCopy);

$result = [
    'strategy' => $cloner->strategyToArray(),
];
$result[] = $user;
// $result[] = $userClone;
$result[] = $userCopy;

dump([
    $userCopy,
    ! ! $userCopy->userProfile,
    count($userCopy->userWallets),
    count($userCopy->userEmails),
]);

dd($result);
