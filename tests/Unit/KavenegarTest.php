<?php

namespace NotificationChannels\Kavenegar\Tests\Unit;

use Illuminate\Contracts\Events\Dispatcher;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use NotificationChannels\Kavenegar\Exceptions\CouldNotSendNotification;
use NotificationChannels\Kavenegar\Kavenegar;
use NotificationChannels\Kavenegar\KavenegarConfig;
use NotificationChannels\Kavenegar\KavenegarMessage;
use NotificationChannels\Kavenegar\KavenegarSmsMessage;
use Kavenegar\KavenegarApi as KavenegarService;

class KavenegarTest extends MockeryTestCase
{
    /** @var KavenegarService */
    protected $kavenegarService;

    /** @var KavenegarConfig */
    protected $config;

    /** @var Dispatcher */
    protected $dispatcher;

    /** @var Kavenegar */
    protected $kavenegar;

    public function setUp(): void
    {
        parent::setUp();

        $this->kavenegarService = Mockery::mock(KavenegarService::class);
        $this->config = Mockery::mock(KavenegarConfig::class);
        $this->dispatcher = Mockery::mock(Dispatcher::class);
        $this->kavenegar = new Kavenegar($this->kavenegarService, $this->config);
    }

    /** @test */
    public function it_can_send_a_sms_message_to_kavenegar()
    {
        $message = new KavenegarSmsMessage('Message text');

        $this->config->shouldReceive('getFrom')
            ->once()
            ->andReturn('+1234567890');

        $this->config->shouldReceive('getDebugTo')
            ->once()
            ->andReturn(null);

        $this->kavenegarService->shouldReceive('Send')
            ->once()
            ->with('+1234567890', '+1111111111', 'Message text')
            ->andReturn([]);

        $this->kavenegar->sendMessage($message, '+1111111111');
    }

    /** @test */
    public function it_will_throw_an_exception_in_case_of_a_missing_from_number()
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Notification was not sent. Missing `from` number.');

        $this->config->shouldReceive('getFrom')
            ->once()
            ->andReturn(null);

        $this->config->shouldReceive('getDebugTo')
            ->once()
            ->andReturn(null);

        $smsMessage = new KavenegarSmsMessage('Message text');

        $this->kavenegar->sendMessage($smsMessage, null);
    }

    /** @test */
    public function it_will_throw_an_exception_in_case_of_an_unrecognized_message_object()
    {
        $this->expectException(CouldNotSendNotification::class);
        $this->expectExceptionMessage('Notification was not sent. Message object class');

        $this->kavenegar->sendMessage(new InvalidMessage(), null);
    }

    /** @test */
    public function it_should_use_universal_to()
    {
        $debugTo = '+1222222222';

        $this->config->shouldReceive('getFrom')
            ->once()
            ->andReturn('+1234567890');

        $this->config->shouldReceive('getDebugTo')
            ->once()
            ->andReturn($debugTo);

        $this->kavenegarService->shouldReceive('Send')
            ->once()
            ->with('+1234567890', $debugTo, 'Message text')
            ->andReturn([]);

        $message = new KavenegarSmsMessage('Message text');

        $this->kavenegar->sendMessage($message, '+1111111111');
    }
}

class InvalidMessage extends KavenegarMessage
{
}
