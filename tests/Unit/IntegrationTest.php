<?php

namespace NotificationChannels\Kavenegar\Tests\Unit;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Notifications\Notification;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use NotificationChannels\Kavenegar\Kavenegar;
use NotificationChannels\Kavenegar\KavenegarChannel;
use NotificationChannels\Kavenegar\KavenegarConfig;
use NotificationChannels\Kavenegar\KavenegarSmsMessage;
use Kavenegar\KavenegarApi as KavenegarService;

class IntegrationTest extends MockeryTestCase
{
    /** @var KavenegarService */
    protected $kavenegarService;

    /** @var Notification */
    protected $notification;

    /** @var Dispatcher */
    protected $events;

    public function setUp(): void
    {
        parent::setUp();

        $this->kavenegarService = Mockery::mock(KavenegarService::class);
        $this->notification = Mockery::mock(Notification::class);
        $this->events = Mockery::mock(Dispatcher::class);
    }

    /** @test */
    public function it_can_send_a_sms_message()
    {
        $this->kavenegarService->shouldReceive('Send')
            ->once()
            ->with('+11111111111', '+22222222222', 'Message text')
            ->andReturn([]);

        $config = new KavenegarConfig([
            'from' => '+11111111111',
        ]);
        $kavenegar = new Kavenegar($this->kavenegarService, $config);
        $channel = new KavenegarChannel($kavenegar, $this->events);

        $message = KavenegarSmsMessage::create('Message text');

        $this->notification->shouldReceive('toKavenegar')->andReturn($message);

        $channel->send(new NotifiableWithAttribute(), $this->notification);
    }
}
