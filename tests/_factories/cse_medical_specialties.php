<?php
/** @var \Module\PDOFactoryMuffin $fm */
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define('cse_medical_specialties')
    ->setMaker(fn($obj) => $obj->pk = 'specialty_id')
    ->setDefinitions([
        'specialty'   => Faker::word(),
        'description' => Faker::sentence(),
    ]);
