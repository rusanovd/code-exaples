<?php

declare(strict_types=1);

namespace More\Amo\Data;

use More\Amo\Data\AmoResponse\AmoEntityContainer;

class AmoLink
{
    private string $from;
    private int $fromId;
    private string $to;
    private int $toId;

    /**
     * @param AmoEntityContainer $amoEntityResponse
     * @return AmoLink
     */
    public static function createFromAmoEntityContainer(AmoEntityContainer $amoEntityResponse): AmoLink
    {
        return (new self())
            ->setFrom((string) $amoEntityResponse->getFieldByKey('from'))
            ->setFromId((int) $amoEntityResponse->getFieldByKey('from_id'))
            ->setTo((string) $amoEntityResponse->getFieldByKey('to'))
            ->setToId((int) $amoEntityResponse->getFieldByKey('to_id'));
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return AmoLink
     */
    public function setFrom(string $from): AmoLink
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return int
     */
    public function getFromId(): int
    {
        return $this->fromId;
    }

    /**
     * @param int $fromId
     * @return AmoLink
     */
    public function setFromId(int $fromId): AmoLink
    {
        $this->fromId = $fromId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return AmoLink
     */
    public function setTo(string $to): AmoLink
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return int
     */
    public function getToId(): int
    {
        return $this->toId;
    }

    /**
     * @param int $toId
     * @return AmoLink
     */
    public function setToId(int $toId): AmoLink
    {
        $this->toId = $toId;

        return $this;
    }
}
