<?php

declare(strict_types=1);

namespace More\Amo\Services;

class AmoConfig
{
    private string $host;
    private string $domain;
    private string $login;
    private string $hash;
    private bool $isEnabled;
    private int $evotorPartnerId;

    /**
     * AmoConfig constructor.
     * @param string $host
     * @param string $domain
     * @param string $login
     * @param string $hash
     * @param bool $isEnabled
     * @param int $evotorPartnerId
     */
    public function __construct(
        string $host,
        string $domain,
        string $login,
        string $hash,
        bool $isEnabled,
        int $evotorPartnerId
    ) {
        $this->host = $host;
        $this->domain = $domain;
        $this->login = $login;
        $this->hash = $hash;
        $this->isEnabled = $isEnabled;
        $this->evotorPartnerId = $evotorPartnerId;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    /**
     * @return int
     */
    public function getEvotorPartnerId(): int
    {
        return $this->evotorPartnerId;
    }
}
