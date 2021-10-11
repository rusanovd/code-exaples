<?php

declare(strict_types=1);

namespace More\Amo\Subscribers;

use More\Amo\Events\AmoContactLeadsResolvedEvent;
use More\Amo\Events\AmoStartReactivationEvent;
use More\Amo\Services\AmoService;
use More\EventDispatcher\AbstractEvent;
use More\Exception\HasUserMessageException;
use More\Integration\SmsProvider\Events\SalonSmsProviderSettingChangedEvent;
use More\Master\Events\MasterLinkedWithUserEvent;
use More\Registration\Events\Wizard\WizardFirstStepCompleteEvent;
use More\Salon\Events\SalonActivatedEvent;
use More\Salon\Events\SalonBaseSettingsChangedEvent;
use More\Salon\Events\SalonBusinessChangedEvent;
use More\Salon\Events\SalonContactsChangedEvent;
use More\Salon\Events\SalonDeactivatedEvent;
use More\Salon\Events\SalonPlanSettingsChangedEvent;
use More\Salon\Events\SalonSettingsCreatedEvent;
use More\Salon\Events\SalonSubscriptionChangedEvent;
use More\SalonTariff\Event\LicenseActivatedEvent;
use More\SalonTariff\Event\LicensePayedEvent;
use More\SalonTariff\Event\LicenseUpdatedEvent;
use More\User\Event\AbstractSalonUserEvent;
use More\User\Event\SalonUserUpdatedEvent;
use More\UserSettings\Events\UserSettingsChangedEvent;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AmoEventSubscriber implements EventSubscriberInterface
{
    private AmoService $amoService;

    public function __construct(AmoService $amoService)
    {
        $this->amoService = $amoService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WizardFirstStepCompleteEvent::name()        => 'processWizardFirstStepComplete',
            SalonBusinessChangedEvent::name()           => 'processSalonBusinessChanged',
            AmoStartReactivationEvent::name()           => 'processAmoLeadReactivationStarted',
            SalonSettingsCreatedEvent::name()           => 'processAmoLeadSettingsCreated',
            LicenseUpdatedEvent::name()                 => 'processAmoLeadLicenseUpdated',
            LicensePayedEvent::name()                   => 'processAmoLeadLicensePaid',
            LicenseActivatedEvent::name()               => 'processAmoLeadLicenseActivated',
            SalonPlanSettingsChangedEvent::name()       => 'processAmoLeadTariffUpdated',
            UserSettingsChangedEvent::name()            => 'processAmoContactOnUserAccessUpdated',
            MasterLinkedWithUserEvent::name()           => 'processMasterAdded',
            SalonBaseSettingsChangedEvent::name()       => 'processSalonBaseSettingsChanged',
            SalonSmsProviderSettingChangedEvent::name() => 'processAmoLeadSmsProviderSettingChanged',
            SalonContactsChangedEvent::name()           => 'processAmoLeadContactInfoSettingsChanged',
//            SalonUserUpdatedEvent::name()               => 'processAmoContactLeadsChanged',
            AmoContactLeadsResolvedEvent::name()        => 'processAmoContactLeadsResolved',
            SalonActivatedEvent::name()                 => 'processAmoLeadActivated',
            SalonDeactivatedEvent::name()               => 'processAmoLeadDeactivated',
            SalonSubscriptionChangedEvent::name()       => 'processSalonSubscriptionChanged',
        ];
    }

    private function logEventName(AbstractEvent $event): void
    {
        $this->amoService->logEventName($event::name());
    }

    public function processAmoLeadActivated(SalonActivatedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnChangeSalonActivity($event->getSalon());
    }

    public function processAmoLeadDeactivated(SalonDeactivatedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnChangeSalonActivity($event->getSalon());
    }

    public function processSalonBaseSettingsChanged(SalonBaseSettingsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnChangeSalonBaseSettings($event->getSalon(), $event->getChanges()->getChangeItems());
    }

    public function processMasterAdded(MasterLinkedWithUserEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->createAmoContactOnAddMaster($event->getSalon(), $event->getMaster());
    }

    /**
     * @param WizardFirstStepCompleteEvent $event
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function processWizardFirstStepComplete(WizardFirstStepCompleteEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnWizardFirstStepComplete($event->getSalon());
    }

    public function processSalonBusinessChanged(SalonBusinessChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnBusinessUpdated($event->getSalon());
    }

    public function processSalonSubscriptionChanged(SalonSubscriptionChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnSubscriptionUpdated($event->getSalon());
    }

    public function processAmoLeadReactivationStarted(AmoStartReactivationEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadStartReactivation($event->getSalon(), $event->getAmoLeadStartReactivationDto());
    }

    /**
     * @param SalonSettingsCreatedEvent $event
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function processAmoLeadSettingsCreated(SalonSettingsCreatedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->createAmoRelatedEntities($event->getSalon(), $event->getUser(), $event->getUserRegisterTargetMetricsDto());
    }

    public function processAmoLeadLicenseUpdated(LicenseUpdatedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnChangeLicenseSettings($event->getLicenseId());
    }

    public function processAmoLeadLicensePaid(LicensePayedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnChangeLicenseSettings($event->getLicense()->getId());
    }

    public function processAmoLeadLicenseActivated(LicenseActivatedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnChangeLicenseActivity($event->getLicense());
    }

    public function processAmoLeadTariffUpdated(SalonPlanSettingsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnChangeTariffSettings($event->getSalon());
    }

    public function processAmoContactOnUserAccessUpdated(UserSettingsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoContactOnUserAccessUpdated($event->getUser(), $event->getSalon());
    }

    public function processAmoLeadSmsProviderSettingChanged(SalonSmsProviderSettingChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnSalonSmsProviderSettingsUpdated($event->getSalon(), $event->getSalonSmsProviderSetting());
    }

    public function processAmoLeadContactInfoSettingsChanged(SalonContactsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoLeadOnSalonContactInfoUpdated($event->getSalon());
    }

    public function processAmoContactLeadsChanged(AbstractSalonUserEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoContactOnChangeLeads($event->getUserId(), $event->getSalonId());
    }

    public function processAmoContactLeadsResolved(AmoContactLeadsResolvedEvent $event): void
    {
        $this->logEventName($event);
        $this->amoService->updateAmoContactOnContactLeadsResolved($event->getAmoContact(), $event->getUser(), $event->getSalon());
    }
}
