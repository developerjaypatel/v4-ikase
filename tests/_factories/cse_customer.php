<?php
/** @var \Module\PDOFactoryMuffin $fm */
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define('cse_customer')->setDefinitions([
    'customer_uuid' => Faker::asciify(str_repeat('*', 15)),
    'eams_no'       => Faker::asciify(str_repeat('*', 10)),

    'cus_name_first'  => Faker::firstName(),
    'cus_name_middle' => Faker::lastName(),
    'cus_name_last'   => Faker::lastName(),
    'cus_name'        => fn($me) => "$me->cus_name_first $me->cus_name_middle $me->cus_name_last",

    'cus_street' => Faker::streetAddress(),
    'cus_city'   => Faker::city(),
    'cus_state'  => Faker::stateAbbr(),
    'cus_zip'    => Faker::postcode(),
    'cus_county' => Faker::citySuffix(),

    'cus_phone' => Faker::phoneNumber(),
    'cus_fax'   => Faker::phoneNumber(),
    'cus_email' => Faker::email(),

    'ip_address' => Faker::ipv4(),
    'cus_ip'     => fn($me) => $me->ip_address,
]);
