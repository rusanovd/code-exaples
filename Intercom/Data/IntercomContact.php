<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Data;

use More\Integration\Intercom\Data\Response\IntercomEntityContainer;
use More\Integration\Intercom\Services\IntercomFieldsMapper;

class IntercomContact
{
    private string $id;
    private int $userId;
    private string $name;
    private string $appId;
    private int $createdTime;
    private int $remoteCreatedTime;
    private int $updatedTime;
    private array $companies;

    /**
     * @param IntercomEntityContainer $intercomEntityContainer
     * @return IntercomContact
     */
    public function createFromIntercomEntityContainer(IntercomEntityContainer $intercomEntityContainer): IntercomContact
    {
        return (new self())
            ->setId($intercomEntityContainer->getId())
            ->setName($intercomEntityContainer->getName())
            ->setAppId($intercomEntityContainer->getAppId())
            ->setUserId($intercomEntityContainer->getContactId())
            ->setCreatedTime($intercomEntityContainer->getCreatedTime())
            ->setRemoteCreatedTime($intercomEntityContainer->getRemoteCreatedTime())
            ->setUpdatedTime($intercomEntityContainer->getUpdatedTime())
            ->setCompanies($intercomEntityContainer->getAttributeMultipleByName(IntercomFieldsMapper::FIELD_COMPANIES));
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return IntercomContact
     */
    public function setId(string $id): IntercomContact
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return IntercomContact
     */
    public function setUserId(int $userId): IntercomContact
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return IntercomContact
     */
    public function setName(string $name): IntercomContact
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->appId;
    }

    /**
     * @param string $appId
     * @return IntercomContact
     */
    public function setAppId(string $appId): IntercomContact
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCreatedTime(): int
    {
        return $this->createdTime;
    }

    /**
     * @param int $createdTime
     * @return IntercomContact
     */
    public function setCreatedTime(int $createdTime): IntercomContact
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    /**
     * @param int $remoteCreatedTime
     * @return IntercomContact
     */
    public function setRemoteCreatedTime(int $remoteCreatedTime): IntercomContact
    {
        $this->remoteCreatedTime = $remoteCreatedTime;

        return $this;
    }

    /**
     * @param int $updatedTime
     * @return IntercomContact
     */
    public function setUpdatedTime(int $updatedTime): IntercomContact
    {
        $this->updatedTime = $updatedTime;

        return $this;
    }

    /**
     * @return array
     */
    public function getCompanies(): array
    {
        return $this->companies;
    }

    /**
     * @param array $companies
     * @return IntercomContact
     */
    public function setCompanies(array $companies): IntercomContact
    {
        $this->companies = $companies;

        return $this;
    }
}
