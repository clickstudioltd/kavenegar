<?php

namespace NotificationChannels\Kavenegar;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\Kavenegar\Exceptions\CouldNotSendNotification;

class KavenegarChannel
{
    /**
     * @var Kavenegar
     */
    protected $kavenegar;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * KavenegarChannel constructor.
     *
     * @param Kavenegar $kavenegar
     * @param Dispatcher $events
     */
    public function __construct(Kavenegar $kavenegar, Dispatcher $events)
    {
        $this->kavenegar = $kavenegar;
        $this->events = $events;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     *
     * @return mixed
     * @throws Exception
     */
    public function send($notifiable, Notification $notification)
    {
        try {
            $message = $notification->toKavenegar($notifiable);

            if (is_string($message)) {
                $message = new KavenegarSmsMessage($message);
            }

            if (! $message instanceof KavenegarMessage) {
                throw CouldNotSendNotification::invalidMessageObject($message);
            }

            $to = $this->getTo($notifiable, $notification, $message);

            return $this->kavenegar->sendMessage($message, $to);
        } catch (Exception $exception) {
            $event = new NotificationFailed(
                $notifiable,
                $notification,
                'kavenegar',
                ['message' => $exception->getMessage(), 'exception' => $exception]
            );

            $this->events->dispatch($event);

            if ($this->kavenegar->config->isIgnoredErrorCode($exception->getCode())) {
                return;
            }

            throw $exception;
        }
    }

    /**
     * Get the address to send a notification to.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @param KavenegarMessage $message
     *
     * @return mixed
     * @throws CouldNotSendNotification
     */
    protected function getTo($notifiable, $notification, $message)
    {
        if ($message->getTo()) {
            return $message->getTo();
        }
        if ($notifiable->routeNotificationFor(self::class, $notification)) {
            return $notifiable->routeNotificationFor(self::class, $notification);
        }
        if ($notifiable->routeNotificationFor('kavenegar', $notification)) {
            return $notifiable->routeNotificationFor('kavenegar', $notification);
        }
        if (isset($notifiable->phone_number)) {
            return $notifiable->phone_number;
        }

        throw CouldNotSendNotification::invalidReceiver();
    }
}
