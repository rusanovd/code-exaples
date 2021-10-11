<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CSalon;
use More\User\Interfaces\UserInterface;

class IntercomContactService
{
    private IntercomContactOptionsService $intercomContactOptionsService;
    private IntercomFieldsService $intercomFieldsService;

    /**
     * IntercomContactService constructor.
     * @param IntercomContactOptionsService $intercomContactOptionsService
     * @param IntercomFieldsService $intercomFieldsService
     */
    public function __construct(IntercomContactOptionsService $intercomContactOptionsService, IntercomFieldsService $intercomFieldsService)
    {
        $this->intercomContactOptionsService = $intercomContactOptionsService;
        $this->intercomFieldsService = $intercomFieldsService;
    }

    /**
     * @param array $contactOptions
     * @param array $companyOptions
     */
    public function setCompanyOptionsToContactOptions(array &$contactOptions, array $companyOptions): void
    {
        if (! empty($companyOptions)) {
            $contactOptions[IntercomFieldsMapper::FIELD_COMPANIES][] = $companyOptions;
        }
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function getContactOptionsByUser(UserInterface $user): array
    {
        $customOptions = array_merge(
            $this->intercomContactOptionsService->getOptionsUtm($user),
            $this->intercomContactOptionsService->getOptionsLetters($user),
            $this->intercomContactOptionsService->getOptionsAmo($user),
            $this->intercomContactOptionsService->getOptionsSalonLinks($user),
            [
                IntercomFieldsMapper::FIELD_ZENDESK_ID  => $user->getZendeskId(),
            ]
        );

        $options = array_merge(
            $this->intercomContactOptionsService->getOptionsIdentification($user),
            [
                IntercomFieldsMapper::FIELD_EMAIL      => $user->getEmail(),
                IntercomFieldsMapper::FIELD_CREATED_AT => $this->intercomFieldsService->getTimeStamp($user->getCreateDate()),
                IntercomFieldsMapper::FIELD_PHONE      => $user->getPhoneString(),
            ]
        );

        return $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);
    }

    /**
     * @param UserInterface $user
     * @param CSalon $salon
     * @return array
     */
    public function getContactOptionsByUserAndCompany(UserInterface $user, CSalon $salon): array
    {
        $options = $this->getContactOptionsByUser($user);
        $options[IntercomFieldsMapper::FIELD_USER_SALON_ID] = $salon->getId();

        return $options;
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    public function getContactOptionsAccess(UserInterface $user): array
    {
        return $this->intercomFieldsService->createWithCustomOptions(
            $this->intercomContactOptionsService->getOptionsIdentification($user),
            $this->intercomContactOptionsService->getOptionsAccess($user)
        );
    }
}
