<?php

namespace App\Providers;

use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Base;
use Illuminate\Support\ServiceProvider;

class FakerServiceProvider extends ServiceProvider
{

    public static array $components = [
        'Farinha de trigo', 'Milho', 'Glutamato monossódico', 'Caseína',
        'Leite em pó', 'Açúcar', 'Maçã', 'Manteiga', 'Propionato de cálcio',
        'Aveia', 'Vitamina D', 'Vitamina B12', 'Goma xantana', 'Sal rosa',
        'Farinha de trigo reforçada com ferro e ácido fólico', 'Amido de milho',
        'Gelatina', 'Banha de porco', 'Ácido cítrico', 'Glicose de milho',
    ];

    protected static array $products = [

    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Generator::class, function () {
            $faker = Factory::create();
            $newClass = new class($faker) extends Base {

                public function component()
                {
                    return static::randomElement(FakerServiceProvider::$components);
                }
            };

            $faker->addProvider($newClass);

            return $faker;
        });
    }
}
