<?php
/** @var \Module\PDOFactoryMuffin $fm */
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define('cse_attorney')->setDefinitions([
    'user_id'           => 'factory|cse_user',
    'customer_id'       => fn ($me) => (int)$fm->find('cse_user', $me->user_id)->customer_id,
    'firm_name'         => Faker::company(),
    'first_name'        => Faker::firstName(),
    'last_name'         => Faker::lastName(),
    'middle_initial'    => Faker::lexify('?'),
    'aka'               => Faker::lexify('????'),
    'phone'             => Faker::phoneNumber(),
    'fax'               => Faker::phoneNumber(),
    'email'             => Faker::companyEmail(),
    'active'            => Faker::randomElement(['Y','N']),
    'attorney_username' => Faker::userName(),
    'attorney_password' => Faker::password(),
    'default_attorney'  => Faker::randomElement(['Y','N']),
    'deleted'           => Faker::randomElement(['Y','N']),
]);
