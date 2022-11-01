# Kavenegar notifications channel for Laravel

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

This package makes it easy to send [Kavenegar notifications](https://docs.kavenegar.io) with Laravel 5.5+, 6.x, 7.x, 8.x & 9.x

## Contents

- [Installation](#installation)
- [Usage](#usage)
	- [Available Message Methods](#available-message-methods)
- [Changelog](#changelog)
- [Testing](#testing)
- [Security](#security)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## Installation

Make sure to add this repository to your `composer.json`:

``` json
    "repositories" : [
        {
            "url": "https://github.com/clickstudioltd/kavenegar.git",
            "type": "git"
        }
    ]
```

You can install the package via composer:

``` bash
composer require clickstudio/kavenegar
```

### Configuration

Add your Kavenegar API key, and From number to your `.env`:

```dotenv
KAVENEGAR_API_KEY=ABCD
KAVENEGAR_FROM=100000000 # optional if using `from` method on message
```

### Advanced Configuration

Run `php artisan vendor:publish --provider="NotificationChannels\Kavenegar\KavenegarProvider"`
```
/config/kavenegar-notification-channel.php
```

#### Suppressing specific errors or all errors

Publish the config using the above command, and edit the `ignored_error_codes` array. You can get the list of
exception codes from [the documentation](https://kavenegar.com/rest.html). 

If you want to suppress all errors, you can set the option to `['*']`. The errors will not be logged but notification
failed events will still be emitted.

## Usage

Now you can use the channel in your `via()` method inside the notification:

``` php
use NotificationChannels\Kavenegar\KavenegarChannel;
use NotificationChannels\Kavenegar\KavenegarSmsMessage;
use Illuminate\Notifications\Notification;

class AccountApproved extends Notification
{
    public function via($notifiable)
    {
        return [KavenegarChannel::class];
    }

    public function toKavenegar($notifiable)
    {
        return (new KavenegarSmsMessage())
            ->content("Your {$notifiable->service} account was approved!");
    }
}
```

In order to let your `Notification` know which phone you are sending to, the channel will look for the `phone_number` attribute of the `Notifiable` model. If you want to override this behavior, add the `routeNotificationForKavenegar` method to your `Notifiable` model.

```php
public function routeNotificationForKavenegar()
{
    return '+1234567890';
}
```

Or simply override the existing To number by calling the `to` method on a `KavenegarMessage` instance.

```php
public function toKavenegar($notifiable)
{
    return (new KavenegarSmsMessage())
        ->content("Your {$notifiable->service} account was approved!")
        ->to('+1234567890');
}
```

### Available Message Methods

#### KavenegarSmsMessage

- `from('')`: Accepts a phone to use as the notification sender.
- `to('')`: Accepts a phone to use as the notification receiver.
- `content('')`: Accepts a string value for the notification body.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email mahangm@gmail.com instead of using the issue tracker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Mahan Heshmati Moghaddam](https://github.com/mahangm)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
