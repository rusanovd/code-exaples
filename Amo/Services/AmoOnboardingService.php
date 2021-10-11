<?php

declare(strict_types=1);

namespace More\Amo\Services;

use CSalon;
use Infrastructure\Metrics\Facade\Metrics;
use More\Amo\Data\AmoContact;
use More\Amo\Data\AmoLead;
use More\Amo\Data\Dto\AmoEntityFieldsDto;
use More\Exception\HasUserMessageException;
use More\References\Country\Interfaces\CountryInterface;
use More\Registration\Data\Onboarding\OnboardingProgress;
use More\Registration\Dto\Onboarding\FourthStepDto;
use More\Registration\Dto\Onboarding\SecondStepDto;
use More\Registration\Dto\Onboarding\ThirdStepDto;
use More\Registration\Dto\Onboarding\VersionB\FifthStepDto_B;
use More\Registration\Dto\Onboarding\VersionB\FourthStepDto_B;
use More\Registration\Dto\Onboarding\VersionB\SecondStepDto_B;
use More\Registration\Dto\Onboarding\VersionB\ThirdStepDto_B;
use More\Registration\Service\Onboarding\OnboardingService;
use More\User\Interfaces\UserInterface;
use Psr\Cache\InvalidArgumentException;

class AmoOnboardingService
{
    private AmoQueueService $amoQueueService;
    private AmoFieldsMapper $amoFieldsMapper;
    private AmoLeadOptionsService $amoLeadOptionsService;
    private AmoLoggerService $amoLoggerService;

    public function __construct(
        AmoQueueService $amoQueueService,
        AmoFieldsMapper $amoFieldsMapper,
        AmoLeadOptionsService $amoLeadOptionsService,
        AmoLoggerService $amoLoggerService
    ) {
        $this->amoQueueService = $amoQueueService;
        $this->amoFieldsMapper = $amoFieldsMapper;
        $this->amoLeadOptionsService = $amoLeadOptionsService;
        $this->amoLoggerService = $amoLoggerService;
    }

    /**
     * На втором экране получаем: имя пользователя, город, страну и должность.
     * @param OnboardingProgress $onboardingProgress
     * @param SecondStepDto $secondStepDto
     * @param CSalon $company
     * @param UserInterface $user
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function processAmoSecondStep(
        OnboardingProgress $onboardingProgress,
        SecondStepDto $secondStepDto,
        CSalon $company,
        UserInterface $user
    ): void {
        if (! $this->amoQueueService->isAmoEnabled() || ! $company->getAmoId() || ! $user->getAmoContactId()) {
            return;
        }

        $contactFields = [
            AmoContact::CONTACT_FIELD_ID_COUNTRY_TITLE => $company->getCountry(),
            AmoContact::CONTACT_FIELD_ID_POSITION      => $secondStepDto->getPosition(),
        ];

        $leadFields =
            $this->amoLeadOptionsService->getOptionsLocation($company) +
            $this->amoLeadOptionsService->getOptionsOnboardingFirstStep($onboardingProgress)
        ;

        $amoContactOnboardingDto = new AmoEntityFieldsDto($user->getAmoContactId(), $contactFields, $secondStepDto->getName());
        $amoLeadOnboardingDto = new AmoEntityFieldsDto($company->getAmoId(), $leadFields, $company->getTitle());

        $this->amoQueueService->setAmoContactToQueue($amoContactOnboardingDto);
        $this->amoQueueService->setAmoLeadToQueue($amoLeadOnboardingDto);

        $this->amoLoggerService->log('processAmoSecondStep', [
            'contact' => $amoContactOnboardingDto->toArray(),
            'lead'    => $amoLeadOnboardingDto->toArray(),
        ]);

        Metrics::increment(AmoMetric::createQueueLeadMetric('onboarding_second_step'));
    }

    /**
     * На третьем экране отправляем: кол-во сотрудников и компаний, а так же сферу бизнес
     * @param OnboardingProgress $onboardingProgress
     * @param ThirdStepDto $thirdStepDto
     * @param CSalon $salon
     */
    public function processAmoThirdStep(OnboardingProgress $onboardingProgress, ThirdStepDto $thirdStepDto, CSalon $salon): void
    {
        if (! $this->amoQueueService->isAmoEnabled() || ! $salon->getAmoId()) {
            return;
        }

        $leadFields = $this->amoLeadOptionsService->getOptionsOnboardingFirstStep($onboardingProgress) + [
            AmoLead::LEAD_BUSINESS_TYPE_FIELD_ID  => $this->amoFieldsMapper->getAmoBusinessTypeId($salon->getBusinessTypeId()),
            AmoLead::LEAD_BUSINESS_GROUP_FIELD_ID => $this->amoFieldsMapper->getAmoBusinessGroupId($salon->getBusinessGroupId()),
            AmoLead::LEAD_FIELD_ID_PLACE_COUNT    => $this->amoFieldsMapper->getAmoBusinessPlaceCountId($thirdStepDto->getPlaceCountId()),
            AmoLead::LEAD_FIELD_ID_STAFF_COUNT_1  => $this->amoFieldsMapper->getAmoBusinessStuffCountId($thirdStepDto->getStaffCountId()),
            AmoLead::LEAD_FIELD_ID_IS_INDIVIDUAL  => OnboardingService::isIndividualMaster($thirdStepDto->getPlaceCountId()),
        ];
        $amoLeadOnboardingDto = new AmoEntityFieldsDto($salon->getAmoId(), $leadFields);
        $this->amoQueueService->setAmoLeadToQueue($amoLeadOnboardingDto);

        $this->amoLoggerService->log('processAmoThirdStep', [
            'lead' => $amoLeadOnboardingDto->toArray(),
        ]);

        Metrics::increment(AmoMetric::createQueueLeadMetric('onboarding_third_step'));
    }

    /**
     * На четвёртом экране отправляем: Основной интерес
     * @param OnboardingProgress $onboardingProgress
     * @param FourthStepDto $fourthStepDto
     * @param CSalon $company
     */
    public function processAmoFourthStep(OnboardingProgress $onboardingProgress, FourthStepDto $fourthStepDto, CSalon $company): void
    {
        if (! $this->amoQueueService->isAmoEnabled() || ! $fourthStepDto->getPurposes() || $company->getAmoId()) {
            return;
        }
        $leadFields = $this->amoLeadOptionsService->getOptionsOnboardingFirstStep($onboardingProgress) + [
            AmoLead::LEAD_FIELD_ID_PURPOSE => $this->amoFieldsMapper->getAmoBusinessPurposesIds($fourthStepDto->getPurposes()),
        ];
        $amoLeadOnboardingDto = new AmoEntityFieldsDto($company->getAmoId(), $leadFields, '');
        $this->amoQueueService->setAmoLeadToQueue($amoLeadOnboardingDto);

        $this->amoLoggerService->log('processAmoFourthStep', [
            'lead' => $amoLeadOnboardingDto->toArray(),
        ]);

        Metrics::increment(AmoMetric::createQueueLeadMetric('onboarding_fourth_step'));
    }

    /*
     * AB test NW-912. Удалить после завершения теста
     */

    public function processAmoSecondStep_B(OnboardingProgress $onboardingProgress, SecondStepDto_B $secondStepDto, CSalon $company, UserInterface $user): void
    {
        if (! $this->amoQueueService->isAmoEnabled() || ! $company->getAmoId() || ! $user->getAmoContactId()) {
            return;
        }

        $contactFields = [
            AmoContact::CONTACT_FIELD_ID_POSITION => $secondStepDto->getPosition(),
        ];

        $leadFields = $this->amoLeadOptionsService->getOptionsOnboardingFirstStep($onboardingProgress) + [
            AmoLead::LEAD_FIELD_ID_PLACE_COUNT   => $this->amoFieldsMapper->getAmoBusinessPlaceCountId($secondStepDto->getPlaceCountId()),
            AmoLead::LEAD_FIELD_ID_IS_INDIVIDUAL => OnboardingService::isIndividualMaster($secondStepDto->getPlaceCountId()),
        ];

        $amoContactOnboardingDto = new AmoEntityFieldsDto($user->getAmoContactId(), $contactFields, $secondStepDto->getName());
        $amoLeadOnboardingDto = new AmoEntityFieldsDto($company->getAmoId(), $leadFields, $company->getTitle());

        $this->amoQueueService->setAmoContactToQueue($amoContactOnboardingDto);
        $this->amoQueueService->setAmoLeadToQueue($amoLeadOnboardingDto);

        $this->amoLoggerService->log('processAmoSecondStep_B', [
            'contact' => $amoContactOnboardingDto->toArray(),
            'lead'    => $amoLeadOnboardingDto->toArray(),
        ]);

        Metrics::increment(AmoMetric::createQueueLeadMetric('onboarding_second_step_b'));
    }

    public function processAmoThirdStep_B(OnboardingProgress $onboardingProgress, ThirdStepDto_B $thirdStepDto, CSalon $salon): void
    {
        if (! $this->amoQueueService->isAmoEnabled() || ! $salon->getAmoId()) {
            return;
        }

        $leadFields = $this->amoLeadOptionsService->getOptionsOnboardingFirstStep($onboardingProgress) + [
            AmoLead::LEAD_BUSINESS_TYPE_FIELD_ID                 => $this->amoFieldsMapper->getAmoBusinessTypeId($salon->getBusinessTypeId()),
            AmoLead::LEAD_BUSINESS_GROUP_FIELD_ID                => $this->amoFieldsMapper->getAmoBusinessGroupId($salon->getBusinessGroupId()),
            AmoLead::LEAD_FIELD_ID_STAFF_COUNT_1                 => $this->amoFieldsMapper->getAmoBusinessStuffCountId($thirdStepDto->getStaffCountId()),
            AmoLead::LEAD_FIELD_ID_IS_PROVIDE_SERVICES_BY_MYSELF => $thirdStepDto->isProvideServicesByMyself(),
        ];

        $amoLeadOnboardingDto = new AmoEntityFieldsDto($salon->getAmoId(), $leadFields, '');
        $this->amoQueueService->setAmoLeadToQueue($amoLeadOnboardingDto);

        $this->amoLoggerService->log('processAmoThirdStep_B', [
            'lead' => $amoLeadOnboardingDto->toArray(),
        ]);

        Metrics::increment(AmoMetric::createQueueLeadMetric('onboarding_third_step_b'));
    }

    public function processAmoFourthStep_B(OnboardingProgress $onboardingProgress, FourthStepDto_B $fourthStepDto_B, CSalon $salon, UserInterface $user): void
    {
        if (! $this->amoQueueService->isAmoEnabled() || ! $salon->getAmoId() || ! $user->getAmoContactId()) {
            return;
        }

        $contactFields = [
            AmoContact::CONTACT_FIELD_ID_COUNTRY_TITLE => $salon->getCountry()->getTitle(),
        ];

        $leadFields =
            $this->amoLeadOptionsService->getOptionsLocation($salon) +
            $this->amoLeadOptionsService->getOptionsOnboardingFirstStep($onboardingProgress) + [
            AmoLead::LEAD_FIELD_ID_REFERRAL_SOURCE => $this->amoFieldsMapper->getAmoReferralSourceId($fourthStepDto_B->getReferralSourceId()),
            AmoLead::LEAD_FIELD_ID_PROMO_CODE      => $fourthStepDto_B->getPromoCode(),
        ];

        $amoContactOnboardingDto = new AmoEntityFieldsDto($user->getAmoContactId(), $contactFields);
        $amoLeadOnboardingDto = new AmoEntityFieldsDto($salon->getAmoId(), $leadFields, '');
        $this->amoQueueService->setAmoLeadToQueue($amoLeadOnboardingDto);
        $this->amoQueueService->setAmoContactToQueue($amoContactOnboardingDto);

        $this->amoLoggerService->log('processAmoFourthStep_B', [
            'contact' => $amoContactOnboardingDto->toArray(),
            'lead'    => $amoLeadOnboardingDto->toArray(),
        ]);

        Metrics::increment(AmoMetric::createQueueLeadMetric('onboarding_fourth_step_b'));
    }

    public function processAmoFifthStep_B(OnboardingProgress $onboardingProgress, FifthStepDto_B $fifthStepDto_B, CSalon $salon): void
    {
        if (! $this->amoQueueService->isAmoEnabled() || ! $salon->getAmoId()) {
            return;
        }

        $leadFields = $this->amoLeadOptionsService->getOptionsOnboardingFirstStep($onboardingProgress) + [
            AmoLead::LEAD_FIELD_ID_PURPOSE           => $this->amoFieldsMapper->getAmoBusinessPurposesIds($fifthStepDto_B->getPurposes()),
        ] + $this->getTimeToCallFifthStepData($fifthStepDto_B, $salon);
        $amoLeadOnboardingDto = new AmoEntityFieldsDto($salon->getAmoId(), $leadFields, '');
        $this->amoQueueService->setAmoLeadToQueue($amoLeadOnboardingDto);

        $this->amoLoggerService->log('processAmoFifthStep_B', [
            'lead' => $amoLeadOnboardingDto->toArray(),
        ]);

        Metrics::increment(AmoMetric::createQueueLeadMetric('onboarding_fifth_step_b'));
    }

    private function getTimeToCallFifthStepData(FifthStepDto_B $fifthStepDto_B, CSalon $salon): array
    {
        if (in_array($salon->getCountryId(), CountryInterface::GLOBAL_COUNTRY_IDS)) {
            return [
                AmoLead::LEAD_FIELD_ID_TIME_TO_CALL      => $fifthStepDto_B->getTimeToCallAsTimestamp(),
                AmoLead::LEAD_FIELD_ID_TIME_TO_CALL_DATE => $fifthStepDto_B->getTimeToCallUsDate(),
                AmoLead::LEAD_FIELD_ID_TIME_TO_CALL_TIME => $fifthStepDto_B->getTimeToCallUsTime(),
            ];
        }

        return [
            AmoLead::LEAD_FIELD_ID_TIME_TO_CALL      => $fifthStepDto_B->getTimeToCallAsTimestamp(),
            AmoLead::LEAD_FIELD_ID_TIME_TO_CALL_DATE => $fifthStepDto_B->getTimeToCallEuDate(),
            AmoLead::LEAD_FIELD_ID_TIME_TO_CALL_TIME => $fifthStepDto_B->getTimeToCallEuTime(),
        ];
    }
}
