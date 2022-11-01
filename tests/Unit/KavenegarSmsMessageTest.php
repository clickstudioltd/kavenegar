<?php

namespace NotificationChannels\Kavenegar\Tests\Unit;

use NotificationChannels\Kavenegar\KavenegarSmsMessage;

class KavenegarSmsMessageTest extends KavenegarMessageTest
{
    public function setUp(): void
    {
        parent::setUp();

        $this->message = new KavenegarSmsMessage();
    }

    /** @test */
    public function it_can_accept_a_message_when_constructing_a_message()
    {
        $message = new KavenegarSmsMessage('myMessage');

        $this->assertEquals('myMessage', $message->content);
    }

    /** @test */
    public function it_provides_a_create_method()
    {
        $message = KavenegarSmsMessage::create('myMessage');

        $this->assertEquals('myMessage', $message->content);
    }
}
