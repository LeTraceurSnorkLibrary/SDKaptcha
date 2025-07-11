<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;

trait WithFaker
{
    /**
     * @var Generator
     */
    protected $faker;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->faker = Factory::create();
    }
}
