<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$configDoctrine = Setup::createAnnotationMetadataConfiguration([ __DIR__ . "/src" ],
    $isDevMode = true,
    $proxyDir = null,
    $cache = null,
    $useSimpleAnnotationReader = false
);

$configDatabase = [
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'port'     => '3306',
    'user'     => 'gzhegow',
    'password' => 'qwe1DSA2zxc3',
    'dbname'   => 'test',
];

/** @noinspection PhpUnhandledExceptionInspection */
$entityManager = EntityManager::create($configDatabase, $configDoctrine);

return $entityManager;
