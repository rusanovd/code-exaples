<?php

namespace More\Amo\Data;

use More\Amo\Data\AmoResponse\AmoEntityContainer;

class AmoConsultingStatus
{
    private int $leadId;
    private string $leadName;
    private int $consultingAmoStatusId;
    private string $consultingAmoStatusName;
    private int $consultingAmoManagerId;
    private string $consultingAmoManagerName;
    private int $integratorAmoManagerId;
    private string $integratorAmoManagerName;
    private int $extraConsultingAmoStatusId;
    private string $extraConsultingAmoStatusName;
    private int $extraConsultingAmoManagerId;
    private string $extraConsultingAmoManagerName;
    private int $salonId;
    private int $modifiedAmoUserId;

    public static function createFromAmoEntityContainer(AmoEntityContainer $amoEntityContainer): AmoConsultingStatus
    {
        return (new self())
            ->setLeadId($amoEntityContainer->getId())
            ->setLeadName($amoEntityContainer->getName())
            ->setConsultingAmoStatusId((int) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_CONSULTING_STATUS_ID, true, AmoEntityContainer::KEY_ENUM))
            ->setConsultingAmoStatusName((string) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_CONSULTING_STATUS_ID, true))
            ->setConsultingAmoManagerId((int) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_CONSULTING_MANAGER_ID, true, AmoEntityContainer::KEY_ENUM))
            ->setConsultingAmoManagerName((string) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_CONSULTING_MANAGER_ID, true))
            ->setExtraConsultingAmoStatusId((int) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_STATUS_ID, true, AmoEntityContainer::KEY_ENUM))
            ->setExtraConsultingAmoStatusName((string) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_STATUS_ID, true))
            ->setExtraConsultingAmoManagerId((int) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_MANAGER_ID, true, AmoEntityContainer::KEY_ENUM))
            ->setExtraConsultingAmoManagerName((string) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_EXTRA_CONSULTING_MANAGER_ID, true))
            ->setIntegratorAmoManagerId((int) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_INTEGRATOR_MANAGER_ID, true, AmoEntityContainer::KEY_ENUM))
            ->setIntegratorAmoManagerName((string) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_INTEGRATOR_MANAGER_ID, true))
            ->setSalonId((int) $amoEntityContainer->getCustomFieldValue(AmoLead::LEAD_FIELD_ID_SALON_ID, true))
            ->setModifiedAmoUserId($amoEntityContainer->getModifiedUserId());
    }

    public function getLeadId(): int
    {
        return $this->leadId;
    }

    private function setLeadId(int $leadId): AmoConsultingStatus
    {
        $this->leadId = $leadId;

        return $this;
    }

    private function setLeadName(string $leadName): AmoConsultingStatus
    {
        $this->leadName = $leadName;

        return $this;
    }

    public function getConsultingAmoStatusId(): int
    {
        return $this->consultingAmoStatusId;
    }

    private function setConsultingAmoStatusId(int $consultingAmoStatusId): AmoConsultingStatus
    {
        $this->consultingAmoStatusId = $consultingAmoStatusId;

        return $this;
    }

    public function getConsultingAmoManagerId(): int
    {
        return $this->consultingAmoManagerId;
    }

    private function setConsultingAmoManagerId(int $consultingAmoManagerId): AmoConsultingStatus
    {
        $this->consultingAmoManagerId = $consultingAmoManagerId;

        return $this;
    }

    public function getIntegratorAmoManagerId(): int
    {
        return $this->integratorAmoManagerId;
    }

    private function setIntegratorAmoManagerId(int $integratorAmoManagerId): AmoConsultingStatus
    {
        $this->integratorAmoManagerId = $integratorAmoManagerId;

        return $this;
    }

    public function getExtraConsultingAmoStatusId(): int
    {
        return $this->extraConsultingAmoStatusId;
    }

    private function setExtraConsultingAmoStatusId(int $extraConsultingAmoStatusId): AmoConsultingStatus
    {
        $this->extraConsultingAmoStatusId = $extraConsultingAmoStatusId;

        return $this;
    }

    public function getExtraConsultingAmoManagerId(): int
    {
        return $this->extraConsultingAmoManagerId;
    }

    private function setExtraConsultingAmoManagerId(int $extraConsultingAmoManagerId): AmoConsultingStatus
    {
        $this->extraConsultingAmoManagerId = $extraConsultingAmoManagerId;

        return $this;
    }

    private function setSalonId(int $salonId): AmoConsultingStatus
    {
        $this->salonId = $salonId;

        return $this;
    }

    public function getSalonId(): int
    {
        return $this->salonId;
    }

    public function getModifiedAmoUserId(): int
    {
        return $this->modifiedAmoUserId;
    }

    private function setModifiedAmoUserId(int $modifiedAmoUserId): AmoConsultingStatus
    {
        $this->modifiedAmoUserId = $modifiedAmoUserId;

        return $this;
    }

    public function isAutoLead(): bool
    {
        return mb_stripos($this->leadName, AmoLead::LEAD_NAME_AUTO_GENERATED) === 0;
    }

    public function isWrongFieldsFormat(): bool
    {
        return
            (! $this->consultingAmoStatusId && $this->consultingAmoStatusName) ||
            (! $this->consultingAmoManagerId && $this->consultingAmoManagerName) ||
            (! $this->integratorAmoManagerId && $this->integratorAmoManagerName) ||
            (! $this->extraConsultingAmoManagerId && $this->extraConsultingAmoManagerName) ||
            (! $this->extraConsultingAmoStatusId && $this->extraConsultingAmoStatusName)
            ;
    }

    private function setConsultingAmoStatusName(string $consultingAmoStatusName): AmoConsultingStatus
    {
        $this->consultingAmoStatusName = $consultingAmoStatusName;

        return $this;
    }

    private function setExtraConsultingAmoStatusName(string $extraConsultingAmoStatusName): AmoConsultingStatus
    {
        $this->extraConsultingAmoStatusName = $extraConsultingAmoStatusName;

        return $this;
    }

    private function setConsultingAmoManagerName(string $consultingAmoManagerName): AmoConsultingStatus
    {
        $this->consultingAmoManagerName = $consultingAmoManagerName;

        return $this;
    }

    private function setExtraConsultingAmoManagerName(string $extraConsultingAmoManagerName): AmoConsultingStatus
    {
        $this->extraConsultingAmoManagerName = $extraConsultingAmoManagerName;

        return $this;
    }

    private function setIntegratorAmoManagerName(string $integratorAmoManagerName): AmoConsultingStatus
    {
        $this->integratorAmoManagerName = $integratorAmoManagerName;

        return $this;
    }
}
