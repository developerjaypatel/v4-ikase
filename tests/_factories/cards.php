<?php
/** @var \Module\PDOFactoryMuffin $fm */
use League\FactoryMuffin\Faker\Facade as Faker;

$fm->define('cards')->setDefinitions([
    'First Name'   => Faker::firstName(),
    'Last Name'    => Faker::lastName(),
    'Job Title'    => Faker::jobTitle(),
    'Company Name' => Faker::company(),
    'Email'        => Faker::email(),
    'Street 1'     => Faker::streetAddress(),
    'Street 2'     => Faker::secondaryAddress(),
    'City'         => Faker::city(),
    'State'        => Faker::stateAbbr(),
    'Zip'          => Faker::postcode(),
    'Phone'        => Faker::optional()->phoneNumber(),
    'Mobile'       => Faker::optional()->phoneNumber(),
    'Fax'          => Faker::optional()->phoneNumber(),
]);
