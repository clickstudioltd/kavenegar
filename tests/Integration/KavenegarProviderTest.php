<?php

declare(strict_types=1);

namespace NotificationChannels\Kavenegar\Tests\Integration;

use NotificationChannels\Kavenegar\Exceptions\InvalidConfigException;
use NotificationChannels\Kavenegar\KavenegarChannel;

class KavenegarProviderTest extends BaseIntegrationTest
{
    public function testThatApplicationCannotCreateChannelWithoutConfig()
    {
        $this->expectException(InvalidConfigException::class);

        $this->app->get(KavenegarChannel::class);
    }

    public function testThatApplicationCannotCreateChannelWithoutApiKey()
    {
        $this->app['config']->set('kavenegar-notification-channel.from', '1234');

        $this->expectException(InvalidConfigException::class);

        $this->app->get(KavenegarChannel::class);
    }

    public function testThatApplicationCreatesChannelWithConfig()
    {
        $this->app['config']->set('kavenegar-notification-channel.api_key', 'abcd');

        $this->assertInstanceOf(KavenegarChannel::class, $this->app->get(KavenegarChannel::class));
    }
}
