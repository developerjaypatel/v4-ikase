<?php
/** @var \Module\PDOFactoryMuffin $fm */
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define('cse_user')->setDefinitions([
    'customer_id' => 'factory|cse_customer',
    'user_uuid'   => Faker::asciify(str_repeat('*', 32)),

    'user_first_name' => Faker::firstName(),
    'user_last_name'  => Faker::lastName(),
    'user_name'       => fn($me) => "$me->user_first_name $me->user_last_name",
    'job'             => Faker::jobTitle(),
    'user_email'      => Faker::companyEmail(),
    'user_cell'       => Faker::phoneNumber(),

    'user_logon' => Faker::userName(),
    'pwd'        => Faker::password(),
    'ip_address' => Faker::ipv4(),

    'activated'        => Faker::randomElement(['Y', 'N']),
    'default_attorney' => Faker::randomElement(['Y', 'N']),
    'deleted'          => Faker::randomElement(['Y', 'N']),
]);
