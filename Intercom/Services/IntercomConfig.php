<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

final class IntercomConfig
{
    public const APP_LAUNCHER_SELECTOR_CLASS = 'js-intercom-button-run-chat';
    public const MAX_LENGTH_CUSTOM_ATTRIBUTE = 255;
    public const INTERCOM_TIMEZONE = 'Europe/Moscow';

    private string $host;
    private bool $isEnabled;
    private string $appID;
    private string $appHash;
    private string $apiVersion;

    /**
     * IntercomConfig constructor.
     * @param string $host
     * @param bool $isEnabled
     * @param string $appID
     * @param string $appHash
     * @param string $apiVersion
     */
    public function __construct(string $host, bool $isEnabled, string $appID, string $appHash, string $apiVersion)
    {
        $this->host = $host;
        $this->isEnabled = $isEnabled;
        $this->appID = $appID;
        $this->appHash = $appHash;
        $this->apiVersion = $apiVersion;
    }

    /**
     * @return string
     */
    public function getAppID(): string
    {
        return $this->appID;
    }

    /**
     * @return string
     */
    public function getAppHash(): string
    {
        return $this->appHash;
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }
}
