<?php

namespace More\Amo\Data\Factories;

use AmoCRM\Client as AmoApiClient;
use AmoCRM\Models\Lead;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Services\AmoFieldsMapper;

class AmoApiLeadFactory
{
    private AmoApiClient $amoApiClient;

    public function __construct(AmoApiClient $amoApiClient)
    {
        $this->amoApiClient = $amoApiClient;
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     * @return Lead
     * @throws \More\Amo\Exceptions\AmoBadParamsException
     */
    public function createLeadByDto(AmoEntityFieldsDto $amoEntityFieldsDto): Lead
    {
        $amoEntityFieldsDto->checkDtoFieldsParam();

        $lead = $this->amoApiClient->lead;

        if (! empty($amoEntityFieldsDto->getName())) {
            $lead->offsetSet(AmoFieldsMapper::FIELD_NAME, $amoEntityFieldsDto->getName());
        }

        if (! empty($amoEntityFieldsDto->getManagerId())) {
            $lead->offsetSet(AmoFieldsMapper::FIELD_MANAGER_ID, $amoEntityFieldsDto->getManagerId());
        }

        foreach ($amoEntityFieldsDto->getFields() as $customFieldId => $customFieldValue) {
            if (is_array($customFieldValue)) {
                $lead->addCustomMultiField($customFieldId, $customFieldValue);
            } else {
                $lead->addCustomField($customFieldId, $customFieldValue);
            }
        }

        return $lead;
    }
}
