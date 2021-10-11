<?php

declare(strict_types=1);

namespace More\Amo\Factories;

use More\Amo\Data\AmoContact;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Amo\Exceptions\AmoBadParamsException;
use More\Amo\Services\AmoContactOptionsService;
use More\User\Interfaces\UserInterface;

class AmoContactFieldsDtoFactory
{
    private AmoContactOptionsService $amoContactOptionsService;

    public function __construct(AmoContactOptionsService $amoContactOptionsService)
    {
        $this->amoContactOptionsService = $amoContactOptionsService;
    }

    /**
     * @param UserInterface|null $user
     * @throws AmoBadParamsException
     */
    private function checkUser(?UserInterface $user): void
    {
        if ($user === null) {
            throw new AmoBadParamsException('User not found');
        }

        if (! $user->getId()) {
            throw new AmoBadParamsException('Bad user id');
        }
    }

    /**
     * @param UserInterface $user
     * @param array $fields
     * @param int[] $leadIds
     * @return AmoEntityFieldsDto
     * @throws AmoBadParamsException
     */
    public function getDtoByUser(
        UserInterface $user,
        array $fields = [],
        array $leadIds = AmoEntityFieldsDto::EMPTY_LEAD_IDS
    ): AmoEntityFieldsDto {
        $this->checkUser($user);

        $fields += $this->amoContactOptionsService->getUserOptions($user);

        return new AmoEntityFieldsDto($user->getAmoContactId(), $fields, $user->getFirstName(), 0, $leadIds);
    }

    public function getDtoByAmoContact(
        AmoContact $contact,
        array $fields = []
    ): AmoEntityFieldsDto {
        return new AmoEntityFieldsDto(
            $contact->getId(),
            $fields,
            '',
            0,
            $contact->getLeadIds() ?: AmoEntityFieldsDto::EMPTY_LEAD_IDS
        );
    }
}
