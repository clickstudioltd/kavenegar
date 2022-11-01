<?php

namespace NotificationChannels\Kavenegar\Tests\Unit;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use Kavenegar\Exceptions\HttpException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use NotificationChannels\Kavenegar\Exceptions\CouldNotSendNotification;
use NotificationChannels\Kavenegar\Kavenegar;
use NotificationChannels\Kavenegar\KavenegarChannel;
use NotificationChannels\Kavenegar\KavenegarConfig;
use NotificationChannels\Kavenegar\KavenegarSmsMessage;

class KavenegarChannelTest extends MockeryTestCase
{
    /** @var Kavenegar */
    protected $kavenegar;

    /** @var Dispatcher */
    protected $dispatcher;

    /** @var KavenegarChannel */
    protected $channel;

    public function setUp(): void
    {
        parent::setUp();

        $this->kavenegar = Mockery::mock(Kavenegar::class);
        $this->dispatcher = Mockery::mock(Dispatcher::class);

        $this->channel = new KavenegarChannel($this->kavenegar, $this->dispatcher);
    }

    /** @test */
    public function it_will_not_send_a_message_without_known_receiver()
    {
        $notifiable = new Notifiable();

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toKavenegar')->andReturn('Message text');

        $this->kavenegar->config = new KavenegarConfig([
            'ignored_error_codes' => [],
        ]);

        $this->dispatcher->shouldReceive('dispatch')
            ->atLeast()->once()
            ->with(Mockery::type(NotificationFailed::class));

        $this->expectException(CouldNotSendNotification::class);

        $result = $this->channel->send($notifiable, $notification);

        $this->assertNull($result);
    }

    /** @test */
    public function it_will_send_a_sms_message_to_the_result_of_the_route_method_of_the_notifiable()
    {
        $notifiable = new NotifiableWithMethod();

        $message = new KavenegarSmsMessage('Message text');

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toKavenegar')->andReturn($message);

        $this->kavenegar->shouldReceive('sendMessage')
            ->atLeast()->once()
            ->with($message, '+1111111111');

        $this->channel->send($notifiable, $notification);
    }

    /** @test */
    public function it_will_convert_a_string_to_a_sms_message()
    {
        $this->kavenegar->shouldReceive('sendMessage')
            ->atLeast()->once()
            ->with(Mockery::type(KavenegarSmsMessage::class), Mockery::any());

        $notifiable = new NotifiableWithAttribute();

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toKavenegar')->andReturn('Message text');

        $this->channel->send($notifiable, $notification);
    }

    /** @test */
    public function it_will_fire_an_event_in_case_of_an_invalid_message()
    {
        $this->kavenegar->config = new KavenegarConfig([
            'ignored_error_codes' => [],
        ]);

        $notifiable = new NotifiableWithAttribute();

        $notification = Mockery::mock(Notification::class);

        // Invalid message.
        $notification->shouldReceive('toKavenegar')->andReturn(-1);

        $this->dispatcher->shouldReceive('dispatch')
            ->atLeast()->once()
            ->with(Mockery::type(NotificationFailed::class));

        $this->expectException(CouldNotSendNotification::class);

        $this->channel->send($notifiable, $notification);
    }

    /** @test */
    public function it_will_ignore_specific_error_codes()
    {
        $this->kavenegar->config = new KavenegarConfig([
            'ignored_error_codes' => [
                400,
            ],
        ]);

        $this->kavenegar->shouldReceive('sendMessage')
            ->andThrow(new HttpException('error', 400));

        $notifiable = new NotifiableWithAttribute();

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toKavenegar')->andReturn('Message text');

        $this->dispatcher->shouldReceive('dispatch')
            ->atLeast()->once()
            ->with(Mockery::type(NotificationFailed::class));

        $this->channel->send($notifiable, $notification);
    }

    /** @test */
    public function it_will_rethrow_non_ignored_error_codes()
    {
        $this->kavenegar->config = new KavenegarConfig([
            'ignored_error_codes' => [
                55555,
            ],
        ]);

        $this->kavenegar->shouldReceive('sendMessage')
            ->andThrow(new HttpException('error', 400));

        $notifiable = new NotifiableWithAttribute();

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toKavenegar')->andReturn('Message text');

        $this->dispatcher->shouldReceive('dispatch')
            ->atLeast()->once()
            ->with(Mockery::type(NotificationFailed::class));

        $this->expectException(HttpException::class);

        $this->channel->send($notifiable, $notification);
    }

    /** @test */
    public function it_will_ignore_all_error_codes()
    {
        $this->kavenegar->config = new KavenegarConfig([
            'ignored_error_codes' => ['*'],
        ]);

        $this->kavenegar->shouldReceive('sendMessage')
            ->andThrow(new HttpException('error', 400));

        $notifiable = new NotifiableWithAttribute();

        $notification = Mockery::mock(Notification::class);
        $notification->shouldReceive('toKavenegar')->andReturn('Message text');

        $this->dispatcher->shouldReceive('dispatch')
            ->atLeast()->once()
            ->with(Mockery::type(NotificationFailed::class));

        $this->channel->send($notifiable, $notification);
    }
}

class Notifiable
{
    public $phone_number = null;

    public function routeNotificationFor() {}
}

class NotifiableWithMethod
{
    public function routeNotificationFor()
    {
        return '+1111111111';
    }
}

class NotifiableWithAttribute
{
    public $phone_number = '+22222222222';

    public function routeNotificationFor() {}
}
