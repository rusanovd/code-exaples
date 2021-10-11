<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use CSalon;
use More\Registration\Dto\Onboarding\FourthStepDto;
use More\Registration\Dto\Onboarding\SecondStepDto;
use More\Registration\Dto\Onboarding\ThirdStepDto;
use More\Registration\Dto\Onboarding\VersionB\FifthStepDto_B;
use More\Registration\Dto\Onboarding\VersionB\FourthStepDto_B;
use More\Registration\Dto\Onboarding\VersionB\SecondStepDto_B;
use More\Registration\Dto\Onboarding\VersionB\ThirdStepDto_B;
use More\Registration\Service\Onboarding\OnboardingDictionaryService;
use More\Registration\Service\Onboarding\OnboardingService;
use More\User\Interfaces\UserInterface;

class IntercomOnboardingService
{
    private IntercomService $intercomService;
    private IntercomCompanyOptionsService $intercomCompanyOptionsService;
    private IntercomContactOptionsService $intercomContactOptionsService;
    private OnboardingDictionaryService $onboardingDictionaryService;
    private IntercomFieldsService $intercomFieldsService;

    public function __construct(
        IntercomService $intercomService,
        IntercomCompanyOptionsService $intercomCompanyOptionsService,
        IntercomContactOptionsService $intercomContactOptionsService,
        OnboardingDictionaryService $onboardingDictionaryService,
        IntercomFieldsService $intercomFieldsService
    ) {
        $this->intercomService = $intercomService;
        $this->intercomCompanyOptionsService = $intercomCompanyOptionsService;
        $this->intercomContactOptionsService = $intercomContactOptionsService;
        $this->onboardingDictionaryService = $onboardingDictionaryService;
        $this->intercomFieldsService = $intercomFieldsService;
    }

    /**
     * На втором экране получаем: имя пользователя, название компании, город, страну и должность.
     * @param SecondStepDto $secondStepDto
     * @param CSalon $salon
     * @param UserInterface $user
     */
    public function processIntercomSecondStep(SecondStepDto $secondStepDto, CSalon $salon, UserInterface $user): void
    {
        $contactOptions = $this->intercomContactOptionsService->getOptionsIdentification($user);
        $contactOptions[IntercomFieldsMapper::FIELD_CUSTOM_ATTRIBUTES] = [
            IntercomFieldsMapper::FIELD_NAME     => $secondStepDto->getName(),
            IntercomFieldsMapper::FIELD_POSITION => $secondStepDto->getPosition(),
        ];
        $this->intercomService->updateContactByQueue($contactOptions);

        $companyOptions = $this->intercomCompanyOptionsService->getOptionsIdentification($salon);
        $companyOptions[IntercomFieldsMapper::FIELD_CUSTOM_ATTRIBUTES] = array_merge(
            $this->intercomCompanyOptionsService->getOptionsLocation($salon),
            [
                IntercomFieldsMapper::FIELD_BRANCH_TIMEZONE => $salon->getTimezoneOriginal(),
            ]
        );

        $this->intercomService->updateCompanyByQueue($companyOptions);
    }

    /**
     * На третьем экране отправляем: кол-во сотрудников и компаний, а так же сферу бизнес
     * @param ThirdStepDto $thirdStepDto
     * @param CSalon $salon
     */
    public function processIntercomThirdStep(ThirdStepDto $thirdStepDto, CSalon $salon): void
    {
        $isIndividual = OnboardingService::isIndividualMaster($thirdStepDto->getPlaceCountId());
        $staffCountValue = $this->onboardingDictionaryService->getStaffCountValue($thirdStepDto->getStaffCountId());
        $placeCountValue = $this->onboardingDictionaryService->getPlaceCountValue($thirdStepDto->getPlaceCountId());

        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
        );

        $options[IntercomFieldsMapper::FIELD_CUSTOM_ATTRIBUTES] = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
            [
                IntercomFieldsMapper::FIELD_PLACES_SIZE   => $placeCountValue,
                IntercomFieldsMapper::FIELD_STAFF_COUNT_1 => $staffCountValue,
                IntercomFieldsMapper::FIELD_IS_INDIVIDUAL => $isIndividual,
            ]
        );

        $this->intercomService->updateCompanyByQueue($options);
    }

    /**
     * На четвёртом экране отправляем: Основной интерес
     * @param FourthStepDto $fourthStepDto
     * @param CSalon $salon
     */
    public function processIntercomFourthStep(FourthStepDto $fourthStepDto, CSalon $salon): void
    {
        if (! $purposes = $this->onboardingDictionaryService->getPurposesValues($fourthStepDto->getPurposes())) {
            return;
        }

        $purposesString = mb_substr(implode(',', $purposes), 0, IntercomConfig::MAX_LENGTH_CUSTOM_ATTRIBUTE);

        $options = $this->intercomCompanyOptionsService->getOptionsIdentification($salon);
        $options[IntercomFieldsMapper::FIELD_CUSTOM_ATTRIBUTES] = [
            IntercomFieldsMapper::FIELD_PRIMARY_USE_CASE => $purposesString,
        ];

        $this->intercomService->updateCompanyByQueue($options);
    }

    /*
     * ______________________
     * AB Test
     */

    /**
     * На втором экране получаем: имя пользователя, количество филиалов, название компании, должность.
     * @param SecondStepDto_B $secondStepDto
     * @param CSalon $salon
     * @param UserInterface $user
     */
    public function processIntercomSecondStep_B(SecondStepDto_B $secondStepDto, CSalon $salon, UserInterface $user): void
    {
        $isIndividual = OnboardingService::isIndividualMaster($secondStepDto->getPlaceCountId());
        $placeCountValue = $this->onboardingDictionaryService->getPlaceCountValue($secondStepDto->getPlaceCountId());

        $contactOptions = $this->intercomContactOptionsService->getOptionsIdentification($user);
        $customOptions = [
            IntercomFieldsMapper::FIELD_NAME     => $secondStepDto->getName(),
            IntercomFieldsMapper::FIELD_POSITION => $secondStepDto->getPosition(),
        ];
        $contactOptions = $this->intercomFieldsService->createWithCustomOptions($contactOptions, $customOptions);

        $this->intercomService->updateContactByQueue($contactOptions);

        $companyOptions = $this->intercomCompanyOptionsService->getOptionsIdentification($salon);
        $customOptions = [
            IntercomFieldsMapper::FIELD_PLACES_SIZE   => $placeCountValue,
            IntercomFieldsMapper::FIELD_IS_INDIVIDUAL => $isIndividual,
            IntercomFieldsMapper::FIELD_NAME          => !$isIndividual ? $secondStepDto->getCompanyTitle() : $secondStepDto->getName(),
        ];
        $companyOptions = $this->intercomFieldsService->createWithCustomOptions($companyOptions, $customOptions);

        $this->intercomService->updateCompanyByQueue($companyOptions);
    }

    /**
     * На третьем экране получаем: сфера бизнеса, тип бизнеса, количество сотрудников, самостоятельное оказание услуг.
     * @param ThirdStepDto_B $thirdStepDto
     * @param CSalon $salon
     */
    public function processIntercomThirdStep_B(ThirdStepDto_B $thirdStepDto, CSalon $salon): void
    {
        $staffCountValue = $this->onboardingDictionaryService->getStaffCountValue($thirdStepDto->getStaffCountId());

        $options = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIdentification($salon),
            $this->intercomCompanyOptionsService->getOptionsIndustry($salon),
        );

        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsIndustryGroup($salon),
            [
                IntercomFieldsMapper::FIELD_STAFF_COUNT_1              => $staffCountValue,
                IntercomFieldsMapper::FIELD_PROVIDE_SERVICES_BY_MYSELF => $thirdStepDto->isProvideServicesByMyself(),
            ]
        );
        $options = $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);

        $this->intercomService->updateCompanyByQueue($options);
    }

    /**
     * На четвертом экране получаем: страна, город, как вы о нас узнали, промо-код.
     * @param FourthStepDto_B $fourthStepDto
     * @param CSalon $salon
     */
    public function processIntercomFourthStep_B(FourthStepDto_B $fourthStepDto, CSalon $salon): void
    {
        $companyOptions = $this->intercomCompanyOptionsService->getOptionsIdentification($salon);
        $customOptions = array_merge(
            $this->intercomCompanyOptionsService->getOptionsLocation($salon),
            [
                IntercomFieldsMapper::FIELD_BRANCH_TIMEZONE => $salon->getTimezoneOriginal(),
                IntercomFieldsMapper::FIELD_PROMO           => $fourthStepDto->getPromoCode(),
                IntercomFieldsMapper::FIELD_REFERRAL_SOURCE => $this->onboardingDictionaryService->getReferralSourceValue($fourthStepDto->getReferralSourceId()),
            ]
        );
        $companyOptions = $this->intercomFieldsService->createWithCustomOptions($companyOptions, $customOptions);

        $this->intercomService->updateCompanyByQueue($companyOptions);
    }

    /**
     * На пятом экране отправляем: Основной интерес
     * @param FifthStepDto_B $fifthStepDto
     * @param CSalon $salon
     */
    public function processIntercomFifthStep_B(FifthStepDto_B $fifthStepDto, CSalon $salon): void
    {
        if (! $purposes = $this->onboardingDictionaryService->getPurposesValues($fifthStepDto->getPurposes())) {
            return;
        }

        $purposesString = mb_substr(implode(',', $purposes), 0, IntercomConfig::MAX_LENGTH_CUSTOM_ATTRIBUTE);

        $options = $this->intercomCompanyOptionsService->getOptionsIdentification($salon);
        $customOptions = [
            IntercomFieldsMapper::FIELD_PRIMARY_USE_CASE => $purposesString,
        ];
        $options = $this->intercomFieldsService->createWithCustomOptions($options, $customOptions);

        $this->intercomService->updateCompanyByQueue($options);
    }
}
