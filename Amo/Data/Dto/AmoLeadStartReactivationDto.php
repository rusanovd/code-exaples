<?php

declare(strict_types=1);

namespace More\Amo\Data\Dto;

final class AmoLeadStartReactivationDto
{
    /**
     * @var \DateTimeImmutable|null
     */
    private $subscribedAtDate;

    /**
     * @var \DateTimeImmutable|null
     */
    private $lastActivationDate;

    /**
     * @var \DateTimeImmutable|null
     */
    private $lastDisactivationDate;

    /**
     * @var \DateTimeImmutable|null
     */
    private $lastRecordCreateDate;

    /**
     * @var \DateTimeImmutable|null
     */
    private $lastStartReactivationDate;

    /**
     * AmoLeadStartReactivationDto constructor.
     * @param \DateTimeImmutable|null $subscribedAtDate
     * @param \DateTimeImmutable|null $lastActivationDate
     * @param \DateTimeImmutable|null $lastDisactivationDate
     * @param \DateTimeImmutable|null $lastRecordCreateDate
     * @param \DateTimeImmutable|null $lastStartReactivationDate
     */
    public function __construct(
        ?\DateTimeImmutable $subscribedAtDate,
        ?\DateTimeImmutable $lastActivationDate,
        ?\DateTimeImmutable $lastDisactivationDate,
        ?\DateTimeImmutable $lastRecordCreateDate,
        ?\DateTimeImmutable $lastStartReactivationDate
    ) {
        $this->subscribedAtDate = $subscribedAtDate;
        $this->lastActivationDate = $lastActivationDate;
        $this->lastDisactivationDate = $lastDisactivationDate;
        $this->lastRecordCreateDate = $lastRecordCreateDate;
        $this->lastStartReactivationDate = $lastStartReactivationDate;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getSubscribedAtDate(): ?\DateTimeImmutable
    {
        return $this->subscribedAtDate;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastActivationDate(): ?\DateTimeImmutable
    {
        return $this->lastActivationDate;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastDisactivationDate(): ?\DateTimeImmutable
    {
        return $this->lastDisactivationDate;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastRecordCreateDate(): ?\DateTimeImmutable
    {
        return $this->lastRecordCreateDate;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastStartReactivationDate(): ?\DateTimeImmutable
    {
        return $this->lastStartReactivationDate;
    }
}
