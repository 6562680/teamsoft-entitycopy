<?php

use Teamsoft\EntityCopy\EntityCopy;
use Teamsoft\EntityCopy\Tests\Demo\Entity;
use Teamsoft\EntityCopy\Tests\Demo\ArrayCollection;

/**
 * @var EntityCopy $cloner
 */

$cloner = require __DIR__ . '/bootstrap/nativeCloner.php';

$entity1 = new Entity([
    'title' => 'entity1',
]);
$entity2 = new Entity([
    'title'    => 'entity2',
    'same'     => $entity1,
    'copy'     => $entity1,
    'children' => [
        $entity1,
    ],
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
$builder = $cloner->from($entity)
    // ->one('same', Entity::class)
    ->one('copy', Entity::class)
    ->one('copy2', Entity::class)
    ->many('children', Entity::class)
    ->child()
    /**/ ->one('copy', Entity::class)
    /**/ ->one('copy2', Entity::class)
    /**/ ->many('children', Entity::class)
    ->endChild();

$entityCopy = $builder->build();

dd([
    'strategy' => $cloner->strategyToArray(),

    'entity'     => $entity,
    'entityCopy' => $entityCopy,
]);
