<?php
/** @var \Module\PDOFactoryMuffin $fm */
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define('cse_bodyparts')->setDefinitions([
    'bodyparts_uuid' => Faker::unique()->asciify(str_repeat('*', 15)),
    'code'           => Faker::unique()->randomNumber(),
    'description'    => Faker::sentence()
]);
