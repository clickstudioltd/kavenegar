<?php

namespace NotificationChannels\Kavenegar;

class KavenegarSmsMessage extends KavenegarMessage
{
    /**
     * Get the from address of this message.
     *
     * @return null|string
     */
    public function getFrom(): ?string
    {
        if ($this->from) {
            return $this->from;
        }

        return null;
    }
}
