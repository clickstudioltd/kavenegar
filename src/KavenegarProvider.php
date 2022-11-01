<?php

namespace NotificationChannels\Kavenegar;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use NotificationChannels\Kavenegar\Exceptions\InvalidConfigException;
use Kavenegar\KavenegarApi as KavenegarService;

class KavenegarProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/kavenegar-notification-channel.php', 'kavenegar-notification-channel');

        $this->publishes([
            __DIR__.'/../config/kavenegar-notification-channel.php' => config_path('kavenegar-notification-channel.php'),
        ]);

        $this->app->bind(KavenegarConfig::class, function () {
            return new KavenegarConfig($this->app['config']['kavenegar-notification-channel']);
        });

        $this->app->singleton(KavenegarService::class, function (Application $app) {
            /** @var KavenegarConfig $config */
            $config = $app->make(KavenegarConfig::class);

            if ($config->getAPIKey()) {
                return new KavenegarService($config->getAPIKey());
            }

            throw InvalidConfigException::missingConfig();
        });

        $this->app->singleton(Kavenegar::class, function (Application $app) {
            return new Kavenegar(
                $app->make(KavenegarService::class),
                $app->make(KavenegarConfig::class)
            );
        });

        $this->app->singleton(KavenegarChannel::class, function (Application $app) {
            return new KavenegarChannel(
                $app->make(Kavenegar::class),
                $app->make(Dispatcher::class)
            );
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            KavenegarConfig::class,
            KavenegarService::class,
            KavenegarChannel::class,
        ];
    }
}
