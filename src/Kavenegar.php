<?php

namespace NotificationChannels\Kavenegar;

use NotificationChannels\Kavenegar\Exceptions\CouldNotSendNotification;

use Kavenegar\KavenegarApi as KavenegarService;

class Kavenegar
{
    /** @var KavenegarService */
    protected $kavenegarService;

    /** @var KavenegarConfig */
    public $config;

    public function __construct(KavenegarService $kavenegarService, KavenegarConfig $config)
    {
        $this->kavenegarService = $kavenegarService;
        $this->config = $config;
    }

    /**
     * Send a KavenegarMessage to a phone number.
     *
     * @param KavenegarMessage $message
     * @param string|null $to
     *
     * @return mixed
     * @throws HttpException
     * @throws CouldNotSendNotification
     */
    public function sendMessage(KavenegarMessage $message, ?string $to)
    {
        if ($message instanceof KavenegarSmsMessage) {
            return $this->sendSmsMessage($message, $to);
        }

        throw CouldNotSendNotification::invalidMessageObject($message);
    }

    /**
     * Send an sms message using the Kavenegar Service.
     *
     * @param KavenegarSmsMessage $message
     * @param string|null $to
     *
     * @return array
     * @throws CouldNotSendNotification
     * @throws HttpException
     */
    protected function sendSmsMessage(KavenegarSmsMessage $message, ?string $to): array
    {
        $debugTo = $this->config->getDebugTo();

        if (!empty($debugTo)) {
            $to = $debugTo;
        }

        $from = $this->getFrom($message);

        if (empty($from)) {
            throw CouldNotSendNotification::missingFrom();
        }

        $content = trim($message->content);

        return $this->kavenegarService->Send($from, $to, $content);
    }

    /**
     * Get the from address from message, or config.
     *
     * @param KavenegarMessage $message
     * @return string|null
     */
    protected function getFrom(KavenegarMessage $message): ?string
    {
        return $message->getFrom() ?: $this->config->getFrom();
    }
}
