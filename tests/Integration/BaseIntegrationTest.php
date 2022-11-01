<?php

declare(strict_types=1);

namespace NotificationChannels\Kavenegar\Tests\Integration;

use NotificationChannels\Kavenegar\KavenegarProvider;
use Orchestra\Testbench\TestCase;

abstract class BaseIntegrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [KavenegarProvider::class];
    }
}
