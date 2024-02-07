<?php
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__);

/**
 * A simpler Faker factory, with the correct default settings for our tests
 * @return \Faker\Generator
 */
function faker() {
    static $faker;
    if (!$faker) {
        $faker = \Faker\Factory::create();
    }
    return $faker;
}
