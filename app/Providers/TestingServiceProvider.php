<?php

namespace App\Providers;

use App\Support\Testing\FakerImageProvider;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Support\ServiceProvider;

use function Clue\StreamFilter\fun;

class TestingServiceProvider extends ServiceProvider
{

    /**
     * Расширяем Faker собственным генератором изображений
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(Generator::class, function () {
            $faker = Factory::create();
            $faker->addProvider(new FakerImageProvider($faker));

            return $faker;
        });
    }

    public function boot(): void
    {
        //
    }
}
