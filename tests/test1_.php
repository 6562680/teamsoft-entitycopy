<?php

use Teamsoft\EntityCopy\EntityCopy;
use Teamsoft\EntityCopy\Tests\Demo\Entity;
use Teamsoft\EntityCopy\Tests\Demo\ArrayCollection;

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

// lets create cloner
$cloner = new EntityCopy();

// of course, we need an unique id to prevent double-copying of single record, we have to get it from entity or from php object
$cloner->setEntityIdAllocator(function ($entity) {
    return spl_object_id($entity);
});

// for example, we have doctine $entityManager
$cloneMap = [];
$entityManager = [];
$cloner
    ->map(function ($entityCloned, $entityOriginal) use (&$cloneMap, &$entityManager) {
        $entityClonedId = spl_object_id($entityCloned);
        $entityOriginalId = spl_object_id($entityOriginal);

        $cloneMap[ $entityOriginalId ] = $entityClonedId;

        return $entityManager[ $entityClonedId ] = $entityCloned;
    });

// for example, we have to store collections into array collection
$cloner
    ->newReducer(new ArrayCollection())
    ->reduce(function (ArrayCollection $carry, $entity, $idx) {
        return $carry->put($idx, $entity);
    });

// then we define the strategy of copy-nesting
$builder = $cloner->from($entity4)
    // ->one('same', Entity::class)
    ->one('copy', Entity::class)
    ->one('copy2', Entity::class)
    ->many('children', Entity::class)
    ->child()
    /**/ ->one('copy', Entity::class)
    /**/ ->one('copy2', Entity::class)
    /**/ ->many('children', Entity::class)
    ->endChild();

// we're getting up!
$entityCopy = $builder->build();

// result here
dd([
    'strategy' => $cloner->strategyToArray(),

    'entity'     => $entity,
    'entityCopy' => $entityCopy,

    'cloneMap'      => $cloneMap,
    'entityManager' => $entityManager,
]);
