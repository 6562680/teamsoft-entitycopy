<?php

use Teamsoft\EntityCopy\EntityCopy;
use Teamsoft\EntityCopy\Tests\Demo\ArrayCollection;

$cloner = new EntityCopy();
$cloner->setEntityIdAllocator(function ($entity) {
    return spl_object_id($entity);
});

$cloner
    ->mapMany(function (array $entitiesCloned) {
        return new ArrayCollection();
    });

return $cloner;
