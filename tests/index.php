<?php

use Teamsoft\EntityCopy\EntityCopy;
use Teamsoft\EntityCopy\Tests\Entity;

require_once __DIR__ . '/../vendor/autoload.php';

$entityManager = [];

$entity1 = new Entity([
    'title' => 'entity1',
]);
$entity2 = new Entity([
    'title' => 'entity2',
    'same'     => $entity1,
    'copy'  => $entity1,
]);
$entity3 = new Entity([
    'title'    => 'entity3',
    'same'     => $entity1,
    'copy'     => $entity1,
    'copy2'    => $entity2,
    'children' => [
        $entity1,
        $entity2,
    ],
]);
$entity4 = new Entity([
    'title'    => 'entity4',
    'same'     => $entity1,
    'copy'     => $entity1,
    'copy2'    => $entity2,
    'children' => [
        $entity1,
        $entity2,
        $entity3,
    ],
]);

$entity = $entity4;

$cloner = ( new EntityCopy($entity) )
    ->setEntityIdAllocator(function () {
        return spl_object_id($this);
    })
    ->pipe(function ($entity) use (&$entityManager) {
        return $entityManager[ spl_object_id($entity) ] = $entity;
    });

$strategy = $cloner
    ->strategy()
    // ->one('same', Entity::class)
    ->one('copy', Entity::class)
    ->one('copy2', Entity::class)
    ->many('children', Entity::class)
    ->child()
    /**/ ->one('copy', Entity::class)
    /**/ ->one('copy2', Entity::class)
    /**/ ->many('children', Entity::class)
    ->endChild();

$entityCopy = $strategy->create();

dd([
    'strategy'      => $cloner->strategyToArray(),
    'entity'        => $entity,
    'entityCopy'    => $entityCopy,
    'entityManager' => $entityManager,
]);
