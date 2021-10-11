<?php

declare(strict_types=1);

namespace More\Amo\Data;

use More\Amo\Data\AmoResponse\AmoEntityContainer;

final class AmoUser
{
    private int $id;
    private string $name;
    private bool $active;

    /**
     * @param AmoEntityContainer $amoEntityResponse
     * @return AmoUser
     */
    public static function createFromAmoEntityContainer(AmoEntityContainer $amoEntityResponse): AmoUser
    {
        return (new self())
            ->setId($amoEntityResponse->getId())
            ->setName((string) $amoEntityResponse->getFieldByKey('name'))
            ->setActive((bool) $amoEntityResponse->getFieldByKey('active'));
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AmoUser
     */
    public function setId(int $id): AmoUser
    {
        $this->id = $id;

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
     * @return AmoUser
     */
    public function setName(string $name): AmoUser
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     * @return AmoUser
     */
    public function setActive(bool $active): AmoUser
    {
        $this->active = $active;

        return $this;
    }
}
