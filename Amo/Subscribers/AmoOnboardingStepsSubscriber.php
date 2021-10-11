<?php

declare(strict_types=1);

namespace More\Amo\Subscribers;

use More\Amo\Services\AmoOnboardingService;
use More\Registration\Events\Onboarding\OnboardingFifthStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingFourthStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingFourthStepCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingSecondStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingSecondStepCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingThirdStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingThirdStepCompleteEvent;

class AmoOnboardingStepsSubscriber
{
    private AmoOnboardingService $amoOnboardingService;

    public function __construct(AmoOnboardingService $amoOnboardingService)
    {
        $this->amoOnboardingService = $amoOnboardingService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OnboardingSecondStepCompleteEvent::name()   => 'processSecondStepComplete',
            OnboardingSecondStep_BCompleteEvent::name() => 'processSecondStep_BComplete',
            OnboardingThirdStepCompleteEvent::name()    => 'processThirdStepComplete',
            OnboardingThirdStep_BCompleteEvent::name()  => 'processThirdStep_BComplete',
            OnboardingFourthStepCompleteEvent::name()   => 'processFourthStepComplete',
            OnboardingFourthStep_BCompleteEvent::name() => 'processFourthStep_BComplete',
            OnboardingFifthStep_BCompleteEvent::name()  => 'processFifthStep_BComplete',
        ];
    }

    /**
     * На втором экране получаем: имя пользователя, название компании, город, страну и должность.
     * @param OnboardingSecondStepCompleteEvent $event
     */
    public function processSecondStepComplete(OnboardingSecondStepCompleteEvent $event): void
    {
        $this->amoOnboardingService->processAmoSecondStep(
            $event->getOnboardingProgress(),
            $event->getSecondStepDto(),
            $event->getSalon(),
            $event->getUser()
        );
    }

    /**
     * @param OnboardingSecondStep_BCompleteEvent $event
     */
    public function processSecondStep_BComplete(OnboardingSecondStep_BCompleteEvent $event): void
    {
        $this->amoOnboardingService->processAmoSecondStep_B(
            $event->getOnboardingProgress(),
            $event->getSecondStepDto_B(),
            $event->getSalon(),
            $event->getUser()
        );
    }

    /**
     * На третьем экране отправляем: кол-во сотрудников и компаний, а так же сферу бизнес
     * @param OnboardingThirdStepCompleteEvent $event
     */
    public function processThirdStepComplete(OnboardingThirdStepCompleteEvent $event): void
    {
        $this->amoOnboardingService->processAmoThirdStep(
            $event->getOnboardingProgress(),
            $event->getThirdStepDto(),
            $event->getSalon()
        );
    }

    /**
     * @param OnboardingThirdStep_BCompleteEvent $event
     */
    public function processThirdStep_BComplete(OnboardingThirdStep_BCompleteEvent $event): void
    {
        $this->amoOnboardingService->processAmoThirdStep_B(
            $event->getOnboardingProgress(),
            $event->getThirdStepDto_B(),
            $event->getSalon()
        );
    }

    /**
     * На четвёртом экране отправляем: Основной интерес
     * @param OnboardingFourthStepCompleteEvent $event
     */
    public function processFourthStepComplete(OnboardingFourthStepCompleteEvent $event): void
    {
        if ($event->getFourthStepDto() === null) {
            return;
        }
        $this->amoOnboardingService->processAmoFourthStep(
            $event->getOnboardingProgress(),
            $event->getFourthStepDto(),
            $event->getSalon()
        );
    }

    public function processFourthStep_BComplete(OnboardingFourthStep_BCompleteEvent $event): void
    {
        $this->amoOnboardingService->processAmoFourthStep_B(
            $event->getOnboardingProgress(),
            $event->getFourthStepDtoB(),
            $event->getSalon(),
            $event->getUser()
        );
    }

    public function processFifthStep_BComplete(OnboardingFifthStep_BCompleteEvent $event): void
    {
        $this->amoOnboardingService->processAmoFifthStep_B(
            $event->getOnboardingProgress(),
            $event->getFifthStepDtoB(),
            $event->getSalon()
        );
    }
}
