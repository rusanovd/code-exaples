<?php

declare(strict_types=1);

namespace More\Amo\OldApi;

use More\Amo\Exceptions\AmoException;

class AmoOldApiClient
{
    public const LIMIT_PER_REQUEST = 500;

    private string $host;
    private string $login;
    private string $hash;
    private AmoCurlClient $curl;
    private string $domain;
    private int $authTime;
    private bool $isEnabled;

    public function __construct(
        string $host,
        string $domain,
        string $login,
        string $hash,
        bool $isEnabled
    ) {
        $this->host = $host;
        $this->domain = $domain;
        $this->login = $login;
        $this->hash = $hash;
        $this->isEnabled = $isEnabled;
        $this->curl = new AmoCurlClient();
    }

    /**
     * @throws AmoException
     */
    private function checkAmoEnabled(): void
    {
        if (! $this->isEnabled) {
            throw new AmoException('Amo is disabled.');
        }
    }

    /**
     * @return void
     * @throws AmoException
     */
    private function authApi(): void
    {
        $this->checkAmoEnabled();

        $user = ['USER_LOGIN' => $this->login, 'USER_HASH' => $this->hash];
        $link = 'https://' . $this->domain . '.' . $this->host . '/private/api/auth.php?type=json';

        $out = $this->curl->post($link, json_encode($user));

        if ($this->curl->getHttpCode() !== 200) {
            throw new AmoException('Bad code connection: ' . $this->curl->getHttpCode());
        }

        $response = json_decode($out, false);

        if (empty($response->response->auth)) {
            throw new AmoException('Authorization failed.');
        }
    }

    public function auth(): void
    {
        if ($this->authTime > time()) {
            return;
        }

        try {
            $this->authApi();
            $this->authTime = time() + 60 * 5; // 5 minutes cache
        } catch (AmoException $e) {
        }
    }

    /**
     * @param array $ids
     * @return array
     * @throws AmoException
     */
    public function deleteContactByIds(array $ids): array
    {
        $this->checkAmoEnabled();

        if (empty($ids)) {
            throw new AmoException('No contacts to delete.');
        }

        $link = 'https://' . $this->domain . '.' . $this->host . '/ajax/contacts/multiple/delete/';
        $data = http_build_query([
            'USER_LOGIN' => $this->login,
            'USER_HASH'  => $this->hash,
            'ACTION'     => 'DELETE',
            'ID'         => $ids,
        ]);

        if (! $response = $this->curl->post($link, $data)) {
            throw new AmoException('Delete contacts failed.');
        }

        $response = (array) json_decode($response, true);

        return $response;
    }
}
