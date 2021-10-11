<?php

namespace More\Amo\Data;

use More\Amo\Data\AmoResponse\AmoEntityContainer;

class AmoResponsible
{
    private int $responsibleAmoUserId;
    private int $salonId;
    private int $modifiedAmoUserId;

    public static function createFromAmoEntityContainer(AmoEntityContainer $amoEntityContainer): AmoResponsible
    {
        return (new self())
            ->setModifiedAmoUserId($amoEntityContainer->getModifiedUserId())
            ->setResponsibleAmoUserId($amoEntityContainer->getResponsibleUserId())
            ->setSalonId((int) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_SALON_ID, true));
    }

    protected function setResponsibleAmoUserId(int $responsibleAmoUserId): AmoResponsible
    {
        $this->responsibleAmoUserId = $responsibleAmoUserId;

        return $this;
    }

    protected function setSalonId(int $salonId): AmoResponsible
    {
        $this->salonId = $salonId;

        return $this;
    }

    public function getSalonId(): int
    {
        return $this->salonId;
    }

    public function getResponsibleAmoUserId(): int
    {
        return $this->responsibleAmoUserId;
    }

    public function getModifiedAmoUserId(): int
    {
        return $this->modifiedAmoUserId;
    }

    public function setModifiedAmoUserId(int $modifiedAmoUserId): AmoResponsible
    {
        $this->modifiedAmoUserId = $modifiedAmoUserId;

        return $this;
    }
}
