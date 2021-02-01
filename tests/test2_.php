<?php

use Doctrine\ORM\EntityManager;
use Teamsoft\EntityCopy\EntityCopy;
use Doctrine\Common\Collections\ArrayCollection;
use Teamsoft\EntityCopy\Tests\Entity\UserEntity;
use Teamsoft\EntityCopy\Tests\Entity\AbstractEntity;
use Teamsoft\EntityCopy\Tests\Entity\EmailEntity;
use Teamsoft\EntityCopy\Tests\Entity\UserWalletEntity;
use Teamsoft\EntityCopy\Tests\Entity\UserProfileEntity;

/**
 * @var EntityManager $entityManager
 */

$entityManager = require_once __DIR__ . '/bootstrap/doctrine.php';
$cloneMap = []; // debug

$cloner = new EntityCopy();
$cloner->setEntityIdAllocator(function (AbstractEntity $entity) {
    return $entity->getId();
});

// process __clone
$cloner
    ->map(function (AbstractEntity $entityCloned, AbstractEntity $entityOriginal) use (&$entityManager, &$cloneMap) {
        $entityClonedId = $entityCloned->getId();
        $entityOriginalId = $entityOriginal->getId();

        $cloneMap[ $entityOriginalId ] = $entityClonedId;

        $entityManager->persist($entityCloned);

        return $entityCloned;
    });

// process iterables
$cloner
    ->newReducer(new ArrayCollection())
    ->reduce(function (ArrayCollection $carry, AbstractEntity $entity, $idx) {
        $carry->set($idx, $entity);

        return $carry;
    });


// new user
$user = new UserEntity();
$user->login = 'user';
/** @noinspection PhpUnhandledExceptionInspection */
$entityManager->persist($user);


// ...with profile: @one-to-one
$userProfile = new UserProfileEntity();
$userProfile->registered_at = new \DateTime();
$userProfile->user = $user;
/** @noinspection PhpUnhandledExceptionInspection */
$entityManager->persist($userProfile);

# $user->userProfile = $userProfile;


// ...with 2 wallets: @one-to-many
$userWallet1 = new UserWalletEntity();
$userWallet1->currency = 'usd';
$userWallet1->value_calculated = 0;
$userWallet1->calculated_at = new \DateTime();
$userWallet1->user = $user;
/** @noinspection PhpUnhandledExceptionInspection */
$entityManager->persist($userWallet1);

$userWallet2 = new UserWalletEntity();
$userWallet2->currency = 'eur';
$userWallet2->value_calculated = 0;
$userWallet2->calculated_at = new \DateTime();
$userWallet2->user = $user;
/** @noinspection PhpUnhandledExceptionInspection */
$entityManager->persist($userWallet2);

# $user->userWallets->add($userWallet1);
# $user->userWallets->add($userWallet2);


// with 2 emails: @many-to-many
$email1 = new EmailEntity();
$email1->email = 'a@a.com';
$email1->users->add($user);
/** @noinspection PhpUnhandledExceptionInspection */
$entityManager->persist($email1);

$email2 = new EmailEntity();
$email2->email = 'b@b.com';
$email2->users->add($user);
/** @noinspection PhpUnhandledExceptionInspection */
$entityManager->persist($email2);

# $user->userEmails->add($userEmail1);
# $user->userEmails->add($userEmail2);


/** @noinspection PhpUnhandledExceptionInspection */
$entityManager->flush();


// $builder = $cloner->from($user)
//     // ->one('same', Entity::class)
//     ->one('copy', Entity::class)
//     ->one('copy2', Entity::class)
//     ->many('children', Entity::class)
//     ->child()
//     /**/ ->one('copy', Entity::class)
//     /**/ ->one('copy2', Entity::class)
//     /**/ ->many('children', Entity::class)
//     ->endChild();

// $entityCopy = $builder->build();

// result here
// dd([
//     'strategy' => $cloner->strategyToArray(),
//
//     'entity'     => $entity,
//     'entityCopy' => $entityCopy,
//
//     'cloneMap'      => $cloneMap,
//     'entityManager' => $entityManager,
// ]);

dd($user);
