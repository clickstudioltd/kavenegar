<?php

namespace NotificationChannels\Kavenegar;

class KavenegarConfig
{
    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function getAPIKey(): ?string
    {
        return $this->config['api_key'] ?? null;
    }

    public function getFrom(): ?string
    {
        return $this->config['from'] ?? null;
    }

    public function getDebugTo(): ?string
    {
        return $this->config['debug_to'] ?? null;
    }

    public function getIgnoredErrorCodes(): array
    {
        return $this->config['ignored_error_codes'] ?? [];
    }

    public function isIgnoredErrorCode(int $code): bool
    {
        if (in_array('*', $this->getIgnoredErrorCodes(), true)) {
            return true;
        }

        return in_array($code, $this->getIgnoredErrorCodes(), true);
    }
}
