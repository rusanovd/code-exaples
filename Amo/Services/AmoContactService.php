<?php

namespace More\Amo\Services;

use CMaster;
use CSalon;
use More\Amo\Data\AmoContact;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Events\AmoContactChangedEvent;
use More\Amo\Events\AmoContactLeadsResolvedEvent;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Exceptions\AmoConfigDisabledException;
use More\Amo\Factories\AmoContactFieldsDtoFactory;
use More\Master\Service\MasterService;
use More\Salon\DataProviders\SalonsDataProvider;
use More\User\Interfaces\UserInterface;
use More\User\Services\UserService;

class AmoContactService
{
    private AmoClient $amoClient;
    private UserService $userService;
    private MasterService $masterService;
    private AmoContactAccessService $amoContactAccessService;
    private AmoLoggerService $amoLoggerService;
    private AmoPhoneFormatter $amoPhoneFormatter;
    private AmoContactFieldsDtoFactory $amoContactFieldsDtoFactory;
    private SalonsDataProvider $salonsDataProvider;

    public function __construct(
        AmoClient $amoClient,
        UserService $userService,
        MasterService $masterService,
        AmoContactAccessService $amoContactAccessService,
        AmoPhoneFormatter $amoPhoneFormatter,
        AmoLoggerService $amoLoggerService,
        AmoContactFieldsDtoFactory $amoContactFieldsDtoFactory,
        SalonsDataProvider $salonsDataProvider
    ) {
        $this->amoClient = $amoClient;
        $this->userService = $userService;
        $this->masterService = $masterService;
        $this->amoContactAccessService = $amoContactAccessService;
        $this->amoLoggerService = $amoLoggerService;
        $this->amoPhoneFormatter = $amoPhoneFormatter;
        $this->amoContactFieldsDtoFactory = $amoContactFieldsDtoFactory;
        $this->salonsDataProvider = $salonsDataProvider;
    }

    /**
     * @param UserInterface $user
     * @param AmoContact[] $contacts
     * @return AmoContact
     */
    private function getContactBestMatch(UserInterface $user, array $contacts): AmoContact
    {
        $contactsWithLeads = [];
        $contactsByPhone = [];
        foreach ($contacts as $contact) {
            if (! empty($contact->getLeadIds())) {
                $contactsWithLeads[] = $contact;
            }

            if (! $phones = $contact->getPhones()) {
                continue;
            }

            foreach ($phones as $phone) {
                if ((int) $user->getPhoneString() === (int) $phone) {
                    $contactsByPhone[] = $contact;
                }
            }
        }

        $cntContactsWithLeads = count($contactsWithLeads);
        if ($cntContactsWithLeads === 1) {
            $contact = reset($contactsWithLeads);
        } else {
            $contact = reset($contactsByPhone);
            if ($cntContactsWithLeads > 1) {
                $this->amoLoggerService->log('getContactBestMatch' . $user->getPhoneString(), [
                    'userId'     => $user->getId(),
                    'phone'      => $user->getPhoneString(),
                    'contacts'   => json_encode($contacts),
                    'ByLeadsCnt' => count($contactsWithLeads),
                    'ByPhoneCnt' => count($contactsByPhone),
                ]);
            }
        }

        if (empty($contact)) {
            $contact = reset($contacts);
        }

        return $contact;
    }

    private function createContactByUser(UserInterface $user, array $leadIds, bool $isUserNeedUpdate): int
    {
        if (! $this->amoPhoneFormatter->checkPhoneLength($user->getPhoneString())) {
            return 0;
        }

        try {
            $amoEntityFieldsDto = $this->amoContactFieldsDtoFactory->getDtoByUser($user, [], $leadIds);
        } catch (AmoBadParamsException $e) {
            return 0;
        }

        try {
            $amoContactId = $this->amoClient->createAmoContactByDto($amoEntityFieldsDto);
        } catch (AmoBadParamsException | AmoConfigDisabledException $e) {
            return 0;
        }

        if (! $amoContactId) {
            return 0;
        }

        if ($isUserNeedUpdate) {
            $this->userService->updateUserAmoContactId($user, $amoContactId);
        }

        $this->amoLoggerService->logApi('createContactByUser', $amoContactId, [
            'userId'       => $user->getId(),
            'contactId'    => $amoContactId,
            'leadIds'      => implode(',', $leadIds),
        ]);

        return $amoContactId;
    }

    /**
     * @param UserInterface $user
     * @return AmoContact|null
     * @throws AmoConfigDisabledException
     */
    private function findContactByUser(UserInterface $user): ?AmoContact
    {
        $this->amoClient->checkAmoEnabled();

        if (! $this->amoPhoneFormatter->checkPhoneLength($user->getPhoneString())) {
            return null;
        }

        if (! $contacts = $this->findContactsByPhone($user->getPhoneString(), $user->getPhoneCode())) {
            return null;
        }

        return $this->getContactBestMatch($user, $contacts);
    }

    /**
     * @param UserInterface $user
     * @param CSalon $salon
     * @return int
     * @throws AmoConfigDisabledException
     */
    public function resolveAmoContactByUserAndSalon(UserInterface $user, Csalon $salon): int
    {
        $this->amoClient->checkAmoEnabled();

        if (! $this->amoPhoneFormatter->checkPhoneLength($user->getPhoneString())) {
            return 0;
        }

        return $this->resolveAmoContactLeadByUserAndSalon($user, $salon);
    }

    /**
     * @param CMaster $master
     * @param CSalon $salon
     * @return int
     * @throws AmoConfigDisabledException
     */
    public function resolveAmoContactByMasterAndSalon(CMaster $master, Csalon $salon): int
    {
        $this->amoClient->checkAmoEnabled();

        if (! $this->masterService->isMasterGoodForIntegration($master)) {
            return 0;
        }

        if (! $user = $this->userService->findUserById($master->getUserId())) {
            return 0;
        }

        return $this->resolveAmoContactByUserAndSalon($user, $salon);
    }

    /**
     * @param UserInterface $user
     * @param CSalon $salon
     * @return int
     * @throws AmoConfigDisabledException
     */
    private function resolveAmoContactLeadByUserAndSalon(UserInterface $user, Csalon $salon): int
    {
        // ищем контакт
        if ($user->getAmoContactId()) {
            $contact = $this->amoClient->findContactById($user->getAmoContactId());
        } else {
            $contact = $this->findContactByUser($user);
        }

        // создаём контакт если нужно
        if ($contact !== null) {
            $amoContactId = $contact->getId();
        } else {
            $leadIds = [];
            if (! $this->amoContactAccessService->hasMasterOnlyAccess($user, $salon)) {
                $leadIds = [$salon->getAmoId()];
            }
            $amoContactId = $this->createContactByUser($user, $leadIds, false);
        }

        // обновляем юзера
        $isAmoContactChanged = false;
        if ($amoContactId > 0 && $user->getAmoContactId() !== $amoContactId) {
            $user = $this->userService->updateUserAmoContactId($user, $amoContactId);
            $isAmoContactChanged = true;
        }

        // проверим существующий контакт amo в сделке
        if ($contact !== null) {
            event(new AmoContactLeadsResolvedEvent($contact, $user, $salon));
        }

        // передадим изменения контакта amo дальше
        if (! $isAmoContactChanged) {
            event(new AmoContactChangedEvent($user));
        }

        return $amoContactId;
    }

    /**
     * @param string $phone
     * @param string $code
     * @return AmoContact[]|null
     * @throws AmoConfigDisabledException
     */
    private function findContactsByPhone(string $phone, string $code = ''): ?array
    {
        $this->amoClient->checkAmoEnabled();

        $phoneWithoutCode = $this->amoPhoneFormatter->getPhoneWithoutCode($phone, $code);

        if (! $amoContacts = $this->amoClient->findContactsByPhone($phoneWithoutCode)) {
            return null;
        }

        $contacts = [];
        foreach ($amoContacts as $amoContact) {
            if (! $phones = $amoContact->getPhones()) {
                continue;
            }
            foreach ($phones as $contactPhone) {
                $contactPhoneWithoutCode = $this->amoPhoneFormatter->getPhoneWithoutCode($contactPhone, $code);

                if (! $this->amoPhoneFormatter->checkPhoneLength($contactPhoneWithoutCode)) {
                    continue;
                }

                if (
                    $contactPhone === $phone ||
                    $phoneWithoutCode === $contactPhoneWithoutCode ||
                    $phoneWithoutCode === $contactPhone ||
                    $contactPhoneWithoutCode === $phone
                ) {
                    $contacts[] = $amoContact;
                    break;
                }
            }
        }

        return $contacts;
    }

    /**
     * @param UserInterface $user
     * @return AmoEntityFieldsDto|null
     * @throws AmoBadParamsException
     */
    public function getAmoContactWithAccessDto(UserInterface $user): ?AmoEntityFieldsDto
    {
        if (! $user->getAmoContactId()) {
            return null;
        }

        $fields = $this->amoContactAccessService->getAmoAccessFields($user);

        return $this->amoContactFieldsDtoFactory->getDtoByUser($user, $fields);
    }

    /**
     * @param int $userId
     * @param int $salonId
     * @return AmoEntityFieldsDto|null
     * @throws AmoConfigDisabledException
     */
    public function getAmoContactDtoByUserAndSalon(int $userId, int $salonId): ?AmoEntityFieldsDto
    {
        if (! $user = $this->userService->findUserById($userId)) {
            return null;
        }

        if (! $user->getAmoContactId()) {
            return null;
        }

        if (! $salon = $this->salonsDataProvider->findSalonById($salonId)) {
            return null;
        }

        if (! $salon->getAmoId()) {
            return null;
        }

        if (! $contact = $this->amoClient->findContactById($user->getAmoContactId())) {
            return null;
        }

        return $this->getAmoContactDtoByContact($contact, $user, $salon);
    }

    public function getAmoContactDtoByContact(AmoContact $contact, UserInterface $user, Csalon $salon): ?AmoEntityFieldsDto
    {
        if (! $user->getAmoContactId() || ! $salon->getAmoId() || ! $contact->getId()) {
            return null;
        }

        $hasChanges = false;
        $fields = [];
        // добавляем контакт в сделку
        if (! $this->amoContactAccessService->hasMasterOnlyAccess($user, $salon) &&
            ! $contact->hasLeadId($salon->getAmoId())
        ) {
            $contact->addLeadId($salon->getAmoId());
            $hasChanges = true;
        }
        // связываем контакт с юзером
        if ($contact->getUserId() !== $user->getId()) {
            $fields[AmoContact::CONTACT_FIELD_ID_USER_ID] = $user->getId();
            $hasChanges = true;
        }

        if (! $hasChanges) {
            return null;
        }

        return $this->amoContactFieldsDtoFactory->getDtoByAmoContact($contact, $fields);
    }
}
