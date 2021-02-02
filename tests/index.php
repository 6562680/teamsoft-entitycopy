<?php

$a = 1;

require_once __DIR__ . '/../vendor/autoload.php';

try {
    // include __DIR__ . '/test1_.php';
    include __DIR__ . '/test2_.php';
} catch (\Throwable $e) {
    dd($e);
}
