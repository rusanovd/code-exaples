<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Subscribers;

use More\Integration\Intercom\Services\IntercomOnboardingService;
use More\Registration\Events\Onboarding\OnboardingFifthStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingFourthStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingFourthStepCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingSecondStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingSecondStepCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingThirdStep_BCompleteEvent;
use More\Registration\Events\Onboarding\OnboardingThirdStepCompleteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IntercomOnboardingStepsSubscriber implements EventSubscriberInterface
{
    private IntercomOnboardingService $intercomOnboardingService;

    /**
     * IntercomOnboardingStepsSubscriber constructor.
     * @param IntercomOnboardingService $intercomOnboardingService
     */
    public function __construct(IntercomOnboardingService $intercomOnboardingService)
    {
        $this->intercomOnboardingService = $intercomOnboardingService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            OnboardingSecondStepCompleteEvent::name()   => 'processSecondStepComplete',
            OnboardingThirdStepCompleteEvent::name()    => 'processThirdStepComplete',
            OnboardingFourthStepCompleteEvent::name()   => 'processFourthStepComplete',
            OnboardingSecondStep_BCompleteEvent::name() => 'processSecondStepComplete_b',
            OnboardingThirdStep_BCompleteEvent::name()  => 'processThirdStepComplete_b',
            OnboardingFourthStep_BCompleteEvent::name() => 'processFourthStepComplete_b',
            OnboardingFifthStep_BCompleteEvent::name()  => 'processFifthStepComplete_b',
        ];
    }

    /**
     * На втором экране получаем: имя пользователя, название компании, город, страну и должность.
     * @param OnboardingSecondStepCompleteEvent $event
     */
    public function processSecondStepComplete(OnboardingSecondStepCompleteEvent $event): void
    {
        $this->intercomOnboardingService->processIntercomSecondStep($event->getSecondStepDto(), $event->getSalon(), $event->getUser());
    }

    /**
     * На третьем экране отправляем: кол-во сотрудников и компаний, а так же сферу бизнес
     * @param OnboardingThirdStepCompleteEvent $event
     */
    public function processThirdStepComplete(OnboardingThirdStepCompleteEvent $event): void
    {
        $this->intercomOnboardingService->processIntercomThirdStep($event->getThirdStepDto(), $event->getSalon());
    }

    /**
     * На четвёртом экране отправляем: Основной интерес
     * @param OnboardingFourthStepCompleteEvent $event
     */
    public function processFourthStepComplete(OnboardingFourthStepCompleteEvent $event): void
    {
        if (empty($event->getFourthStepDto()->getPurposes())) {
            return;
        }
        $this->intercomOnboardingService->processIntercomFourthStep($event->getFourthStepDto(), $event->getSalon());
    }

    /**
     * @param OnboardingSecondStep_BCompleteEvent $event
     */
    public function processSecondStepComplete_b(OnboardingSecondStep_BCompleteEvent $event): void
    {
        $this->intercomOnboardingService->processIntercomSecondStep_B($event->getSecondStepDto_B(), $event->getSalon(), $event->getUser());
    }

    /**
     * На третьем экране отправляем: кол-во сотрудников и компаний, а так же сферу бизнес
     * @param OnboardingThirdStep_BCompleteEvent $event
     */
    public function processThirdStepComplete_b(OnboardingThirdStep_BCompleteEvent $event): void
    {
        $this->intercomOnboardingService->processIntercomThirdStep_B($event->getThirdStepDto_B(), $event->getSalon());
    }

    /**
     * На четвёртом экране отправляем: Основной интерес
     * @param OnboardingFourthStep_BCompleteEvent $event
     */
    public function processFourthStepComplete_b(OnboardingFourthStep_BCompleteEvent $event): void
    {
        $this->intercomOnboardingService->processIntercomFourthStep_B($event->getFourthStepDtoB(), $event->getSalon());
    }

    /**
     * На четвёртом экране отправляем: Основной интерес
     * @param OnboardingFifthStep_BCompleteEvent $event
     */
    public function processFifthStepComplete_b(OnboardingFifthStep_BCompleteEvent $event): void
    {
        if (empty($event->getFifthStepDtoB()->getPurposes())) {
            return;
        }
        $this->intercomOnboardingService->processIntercomFifthStep_B($event->getFifthStepDtoB(), $event->getSalon());
    }
}
