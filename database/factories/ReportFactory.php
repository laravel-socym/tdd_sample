<?php

use Faker\Generator as Faker;

$factory->define(App\Report::class, function (Faker $faker) {
    return [
        'visit_date' => $faker->date(),
        'detail' => $faker->realText(),
    ];
});
