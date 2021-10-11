<?php

declare(strict_types=1);

namespace More\Amo\Services;

use More\Amo\Data\AmoContact;
use More\User\Interfaces\UserInterface;

class AmoContactOptionsService
{
    public function getUserOptions(UserInterface $user): array
    {
        return [
            AmoContact::CONTACT_FIELD_ID_USER_ID       => $user->getId(),
            AmoContact::CONTACT_FIELD_ID_PHONE         => $user->getPhoneString(),
            AmoContact::CONTACT_FIELD_ID_EMAIL         => $user->getEmail(),
            AmoContact::CONTACT_FIELD_ID_COUNTRY_TITLE => $user->getCity()->getCountry()->getTitle(),
        ];
    }
}
