<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Data;

use More\Integration\Intercom\Data\Response\IntercomEntityContainer;

class IntercomCompany
{
    private string $id;
    private int $salonId;
    private string $name;
    private string $appId;
    private int $createdTime;
    private int $remoteCreatedTime;
    private int $updatedTime;

    /**
     * @param IntercomEntityContainer $intercomEntityContainer
     * @return IntercomCompany
     */
    public function createFromIntercomEntityContainer(IntercomEntityContainer $intercomEntityContainer): IntercomCompany
    {
        return (new self())
            ->setId($intercomEntityContainer->getId())
            ->setName($intercomEntityContainer->getName())
            ->setAppId($intercomEntityContainer->getAppId())
            ->setSalonId($intercomEntityContainer->getCompanyId())
            ->setCreatedTime($intercomEntityContainer->getCreatedTime())
            ->setRemoteCreatedTime($intercomEntityContainer->getRemoteCreatedTime())
            ->setUpdatedTime($intercomEntityContainer->getUpdatedTime());
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
     * @return IntercomCompany
     */
    public function setId(string $id): IntercomCompany
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getSalonId(): int
    {
        return $this->salonId;
    }

    /**
     * @param int $salonId
     * @return IntercomCompany
     */
    public function setSalonId(int $salonId): IntercomCompany
    {
        $this->salonId = $salonId;

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
     * @return IntercomCompany
     */
    public function setName(string $name): IntercomCompany
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
     * @return IntercomCompany
     */
    public function setAppId(string $appId): IntercomCompany
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
     * @return IntercomCompany
     */
    public function setCreatedTime(int $createdTime): IntercomCompany
    {
        $this->createdTime = $createdTime;

        return $this;
    }

    /**
     * @return int
     */
    public function getRemoteCreatedTime(): int
    {
        return $this->remoteCreatedTime;
    }

    /**
     * @param int $remoteCreatedTime
     * @return IntercomCompany
     */
    public function setRemoteCreatedTime(int $remoteCreatedTime): IntercomCompany
    {
        $this->remoteCreatedTime = $remoteCreatedTime;

        return $this;
    }

    /**
     * @param int $updatedTime
     * @return IntercomCompany
     */
    public function setUpdatedTime(int $updatedTime): IntercomCompany
    {
        $this->updatedTime = $updatedTime;

        return $this;
    }
}
