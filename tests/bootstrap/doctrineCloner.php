<?php

/**
 * @var EntityManager $entityManager
 */
if (! isset($entityManager)) {
    throw new \Teamsoft\EntityCopy\Exceptions\Logic\InvalidArgumentException('EntityManager should be defined');
}

use Doctrine\ORM\EntityManager;
use Teamsoft\EntityCopy\EntityCopy;
use Doctrine\Common\Collections\ArrayCollection;
use Teamsoft\EntityCopy\Tests\Entity\AbstractEntity;

$cloner = new EntityCopy();
$cloner->setEntityIdAllocator(function (AbstractEntity $entity) {
    return $entity->getId();
});

$cloner
    ->mapOne(function (AbstractEntity $entityCloned, $entityOriginal) use (&$entityManager) {
        dump($entityCloned, $entityOriginal);

        $entityManager->persist($entityCloned);

        return $entityCloned;
    });

$cloner
    ->mapMany(function (array $entitiesCloned) {
        return new ArrayCollection($entitiesCloned);
    });

return $cloner;
