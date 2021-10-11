<?php

namespace More\Amo\Services;

use AmoCRM\Client;
use Infrastructure\Metrics\Facade\Metrics;
use More\Amo\Data\AmoContact;
use More\Amo\Data\AmoLead;
use More\Amo\Data\AmoResponse\AmoEntityContainer;
use More\Amo\Data\AmoTask;
use More\Amo\Data\AmoUser;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Data\Factories\AmoApiContactFactory;
use More\Amo\Data\Factories\AmoApiLeadFactory;
use More\Amo\Data\Factories\AmoApiTaskFactory;
use More\Amo\Exceptions\AmoConfigDisabledException;

class AmoClient
{
    private string $amoHost;
    private Client $amoApiClient;
    private bool $enabled;
    private AmoApiTaskFactory $amoApiTaskFactory;
    private AmoApiContactFactory $amoApiContactFactory;
    private AmoApiLeadFactory $amoApiLeadFactory;

    public function __construct(
        string $domain,
        string $login,
        string $hash,
        bool $enabled
    ) {
        $this->amoHost = $domain;
        $this->amoApiClient = new Client($domain, $login, $hash);
        $this->amoApiContactFactory = new AmoApiContactFactory($this->amoApiClient);
        $this->amoApiLeadFactory = new AmoApiLeadFactory($this->amoApiClient);
        $this->amoApiTaskFactory = new AmoApiTaskFactory($this->amoApiClient);
        $this->enabled = $enabled;
    }

    public function getAmoHost(): string
    {
        return $this->amoHost;
    }

    /**
     * @param array $ids
     * @return AmoContact[]|null
     */
    public function findContactsByIds(array $ids): ?array
    {
        if (! $this->enabled || empty($ids)) {
            return null;
        }

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_CONTACT_FIND_BY_IDS));

        return array_map(static function ($amoContactArray) {
            return AmoContact::createFromAmoEntityContainer(new AmoEntityContainer($amoContactArray));
        }, $this->amoApiClient->contact->apiList(['id' => $ids]));
    }

    /**
     * @param string $phone
     * @return AmoContact[]|null
     */
    public function findContactsByPhone(string $phone): ?array
    {
        if (! $this->enabled || empty($phone)) {
            return null;
        }

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_CONTACT_FIND_BY_PHONE));

        return array_map(static function ($contactList) {
            return AmoContact::createFromAmoEntityContainer(new AmoEntityContainer($contactList));
        }, $this->amoApiClient->contact->apiList(['query' => $phone]));
    }

    /**
     * @param int $contactId
     * @return AmoContact|null
     */
    public function findContactById(int $contactId): ?AmoContact
    {
        if (! $this->enabled) {
            return null;
        }

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_CONTACT_FIND_BY_ID));

        $amoApiList = $this->amoApiClient->contact->apiList([
            'id'         => $contactId,
            'limit_rows' => 1,
        ]);
        $amoContact = reset($amoApiList);

        return $amoContact
            ? AmoContact::createFromAmoEntityContainer(new AmoEntityContainer($amoContact))
            : null;
    }

    public function addTask(AmoTask $amoTask): int
    {
        if (! $this->enabled) {
            return 0;
        }

        $apiTask = $this->amoApiTaskFactory->createFromYcAmoTask($amoTask);

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_TASK_CREATE));

        return (int) $apiTask->apiAdd();
    }

    /**
     * @param string $query
     * @param int $offset
     * @param int $limit
     * @return AmoLead[]
     */
    public function getLeads(string $query = '', int $offset = 0, int $limit = 0): array
    {
        if (! $this->enabled) {
            return [];
        }

        $params = [
            'limit_rows'   => $limit,
            'limit_offset' => $offset,
            'query'        => $query,
        ];

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_LEAD_GET));

        return array_map(static function ($amoLeadResponseArray) {
            return AmoLead::createFromAmoEntityContainer(new AmoEntityContainer($amoLeadResponseArray));
        }, $this->amoApiClient->lead->apiList($params));
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return AmoContact[]|null
     */
    public function getContacts(int $limit, int $offset): ?array
    {
        if (! $this->enabled) {
            return null;
        }

        $params = [
            'limit_rows'   => $limit,
            'limit_offset' => $offset,
        ];

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_CONTACT_GET));

        return array_map(static function ($contact) {
            return AmoContact::createFromAmoEntityContainer(new AmoEntityContainer($contact));
        }, $this->amoApiClient->contact->apiList($params));
    }

    /**
     * Найти лида AmoCRM по ID
     *
     * @param int $amoLeadId
     * @return AmoLead|null
     */
    public function findAmoLeadById(int $amoLeadId): ?AmoLead
    {
        if (! $this->enabled || ! $amoLeadId) {
            return null;
        }

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_LEAD_FIND_BY_ID));

        $amoLeadResponseArray = $this->amoApiClient->lead->apiList(['id' => $amoLeadId]);
        $amoLeadResponse = reset($amoLeadResponseArray);

        return $amoLeadResponse
            ? AmoLead::createFromAmoEntityContainer(new AmoEntityContainer($amoLeadResponse))
            : null;
    }

    /**
     * Найти пользователя AmoCRM по ID
     *
     * @param int $amoUserId
     * @return AmoUser|null
     */
    public function findAmoUserById(int $amoUserId): ?AmoUser
    {
        if (! $this->enabled || ! $amoUserId) {
            return null;
        }

        $params = [
            'with' => ['users'],
        ];

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_ACCOUNT_FIND_BY_ID));

        $amoResponse = $this->amoApiClient->account->apiCurrent(false, $params);

        if (empty($amoResponse['users'])) {
            return null;
        }

        foreach ($amoResponse['users'] as $amoUserResponse) {
            if (isset($amoUserResponse['id']) && (int) $amoUserResponse['id'] === $amoUserId) {
                return AmoUser::createFromAmoEntityContainer(new AmoEntityContainer($amoUserResponse));
            }
        }

        return null;
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     * @return int
     * @throws \AmoCRM\Exception
     * @throws AmoConfigDisabledException
     * @throws \More\Amo\Exceptions\AmoBadParamsException
     */
    public function createAmoLeadByDto(AmoEntityFieldsDto $amoEntityFieldsDto): int
    {
        $this->checkAmoEnabled();
        $lead = $this->amoApiLeadFactory->createLeadByDto($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_LEAD_CREATE));

        return (int) $lead->apiAdd();
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     * @return int
     * @throws AmoConfigDisabledException
     * @throws \More\Amo\Exceptions\AmoBadParamsException
     */
    public function createAmoContactByDto(AmoEntityFieldsDto $amoEntityFieldsDto): int
    {
        $this->checkAmoEnabled();
        $contact = $this->amoApiContactFactory->createContactByDto($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_CONTACT_CREATE_BY_DTO));

        return (int) $contact->apiAdd();
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     * @return bool
     * @throws AmoConfigDisabledException
     * @throws \AmoCRM\Exception
     * @throws \More\Amo\Exceptions\AmoBadParamsException
     */
    public function updateAmoLeadByDto(AmoEntityFieldsDto $amoEntityFieldsDto): bool
    {
        $this->checkAmoEnabled();
        $lead = $this->amoApiLeadFactory->createLeadByDto($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_LEAD_UPDATE));

        return $lead->apiUpdate($amoEntityFieldsDto->getId());
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     * @return bool
     * @throws AmoConfigDisabledException
     * @throws \AmoCRM\Exception
     * @throws \More\Amo\Exceptions\AmoBadParamsException
     */
    public function updateAmoContactByDto(AmoEntityFieldsDto $amoEntityFieldsDto): bool
    {
        $this->checkAmoEnabled();
        $contact = $this->amoApiContactFactory->createContactByDto($amoEntityFieldsDto);

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_CONTACT_UPDATE));

        return $contact->apiUpdate($amoEntityFieldsDto->getId());
    }

    /**
     * @return bool
     */
    public function isAmoEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @throws AmoConfigDisabledException
     */
    public function checkAmoEnabled(): void
    {
        if (! $this->enabled) {
            throw new AmoConfigDisabledException;
        }
    }

    /**
     * @param int $amoContactId
     * @param array $leadIds
     * @return bool
     * @throws \AmoCRM\Exception
     */
    public function updateAmoContactLeads(int $amoContactId, array $leadIds): bool
    {
        if (! $this->enabled) {
            return true;
        }

        $contact = $this->amoApiContactFactory->createContactWithLeads($leadIds);

        Metrics::increment(AmoMetric::createRequestMetric(AmoMetric::METRIC_REQUEST_CONTACT_UPDATE_LEADS));

        return $contact->apiUpdate($amoContactId);
    }
}
