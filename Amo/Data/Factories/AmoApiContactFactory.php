<?php

namespace More\Amo\Data\Factories;

use AmoCRM\Client as AmoApiClient;
use AmoCRM\Models\Contact;
use More\Amo\Data\AmoContact;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Services\AmoFieldsMapper;

class AmoApiContactFactory
{
    private AmoApiClient $amoApiClient;

    /**
     * AmoTaskFactory constructor.
     * @param AmoApiClient $amoApiClient
     */
    public function __construct(AmoApiClient $amoApiClient)
    {
        $this->amoApiClient = $amoApiClient;
    }

    /**
     * @param AmoEntityFieldsDto $amoEntityFieldsDto
     * @return Contact
     * @throws \More\Amo\Exceptions\AmoBadParamsException
     */
    public function createContactByDto(AmoEntityFieldsDto $amoEntityFieldsDto): Contact
    {
        $amoEntityFieldsDto->checkDtoFieldsParam();

        $contact = $this->amoApiClient->contact;

        if (! empty($amoEntityFieldsDto->getName())) {
            $contact->offsetSet(AmoFieldsMapper::FIELD_NAME, $amoEntityFieldsDto->getName());
        }

        if ($amoEntityFieldsDto->isLeadsChanged()) {
            $contact->offsetSet(AmoFieldsMapper::FIELD_LINKED_LEAD_IDS, $amoEntityFieldsDto->getLeadIds());
        }

        foreach ($amoEntityFieldsDto->getFields() as $customFieldId => $customFieldValue) {
            if (is_array($customFieldValue)) {
                $contact->addCustomMultiField($customFieldId, $customFieldValue);
            } elseif ($customFieldId === AmoContact::CONTACT_FIELD_ID_PHONE) {
                $contact->addCustomField($customFieldId, $customFieldValue, AmoContact::CONTACT_FIELD_ENUM_PHONE);
            } elseif ($customFieldId === AmoContact::CONTACT_FIELD_ID_EMAIL) {
                $contact->addCustomField($customFieldId, $customFieldValue, AmoContact::CONTACT_FIELD_ENUM_EMAIL);
            } else {
                $contact->addCustomField($customFieldId, $customFieldValue);
            }
        }

        return $contact;
    }

    /**
     * @param array $leadIds
     * @return Contact
     */
    public function createContactWithLeads(array $leadIds): Contact
    {
        $contact = $this->amoApiClient->contact;
        $contact->offsetSet(AmoFieldsMapper::FIELD_LINKED_LEAD_IDS, $leadIds);

        return $contact;
    }
}
