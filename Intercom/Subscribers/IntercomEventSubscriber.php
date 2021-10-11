<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Subscribers;

use Infrastructure\Metrics\Facade\Metrics;
use More\Amo\Events\AmoCallBackChangeConsultingStatusEvent;
use More\Amo\Events\AmoCallBackChangeLeadManagerEvent;
use More\Amo\Events\AmoContactChangedEvent;
use More\EventDispatcher\AbstractEvent;
use More\Exception\HasUserMessageException;
use More\Integration\Intercom\Services\IntercomMetric;
use More\Integration\Intercom\Services\IntercomService;
use More\Master\Events\AbstractMasterEvent;
use More\Master\Events\MasterCreatedEvent;
use More\Master\Events\MasterDeletedEvent;
use More\Master\Events\MasterFiredEvent;
use More\Master\Events\MasterRestoredEvent;
use More\Master\Events\MasterUnFiredEvent;
use More\Salon\Events\SalonActivatedEvent;
use More\Salon\Events\SalonBaseSettingsChangedEvent;
use More\Salon\Events\SalonBusinessChangedEvent;
use More\Salon\Events\SalonContactsChangedEvent;
use More\Salon\Events\SalonDeactivatedEvent;
use More\Salon\Events\SalonPlanSettingsChangedEvent;
use More\Salon\Events\SalonSettingsCreatedEvent;
use More\SalonTariff\Event\LicenseActivatedEvent;
use More\SalonTariff\Event\LicensePayedEvent;
use More\SalonTariff\Event\LicenseUpdatedEvent;
use More\UserSettings\Events\UserSettingsChangedEvent;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IntercomEventSubscriber implements EventSubscriberInterface
{
    private IntercomService $intercomService;

    /**
     * IntercomEventSubscriber constructor.
     * @param IntercomService $intercomService
     */
    public function __construct(IntercomService $intercomService)
    {
        $this->intercomService = $intercomService;
    }

    /**
     * @return array|string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SalonContactsChangedEvent::name()              => 'processCompanyContactInfoChanged',
            SalonBaseSettingsChangedEvent::name()          => 'processCompanyMainSettingsChanged',
            SalonBusinessChangedEvent::name()              => 'processSalonBusinessChanged',
            SalonSettingsCreatedEvent::name()              => 'processCompanySettingsCreated',
            LicenseUpdatedEvent::name()                    => 'processCompanyLicenseChanged',
            LicensePayedEvent::name()                      => 'processCompanyLicensePaid',
            LicenseActivatedEvent::name()                  => 'processCompanyLicenseActivated',
            SalonActivatedEvent::name()                    => 'processCompanyActivated',
            SalonDeactivatedEvent::name()                  => 'processCompanyDeactivated',
            AmoCallBackChangeLeadManagerEvent::name()      => 'processCompanyManagerChanged',
            AmoCallBackChangeConsultingStatusEvent::name() => 'processCompanyStatusConsultingChanged',
            AmoContactChangedEvent::name()                 => 'processContactAmoChanged',
            SalonPlanSettingsChangedEvent::name()          => 'processCompanyTariffChanged',
            UserSettingsChangedEvent::name()               => 'processUserSettingsChanged',
            MasterCreatedEvent::name()                     => 'processCompanyCountMastersChanged',
            MasterDeletedEvent::name()                     => 'processCompanyCountMastersChanged',
            MasterRestoredEvent::name()                    => 'processCompanyCountMastersChanged',
            MasterFiredEvent::name()                       => 'processCompanyCountMastersChanged',
            MasterUnFiredEvent::name()                     => 'processCompanyCountMastersChanged',
        ];
    }

    /**
     * @param AbstractEvent $event
     */
    private function logEventName(AbstractEvent $event): void
    {
        $this->intercomService->logEventName($event::name());
    }

    /**
     * @param SalonBusinessChangedEvent $event
     */
    public function processSalonBusinessChanged(SalonBusinessChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateIntercomBusinessChanged($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_BUSINESS_CHANGED));
    }

    /**
     * @param AbstractMasterEvent $event
     */
    public function processCompanyCountMastersChanged(AbstractMasterEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateIntercomCompanyStaff($event->getMaster());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_MASTERS_NUM_CHANGED));
    }

    /**
     * @param UserSettingsChangedEvent $event
     */
    public function processUserSettingsChanged(UserSettingsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateIntercomContactByUser($event->getUser());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_CONTACT_SETTINGS_CHANGED));
    }

    /**
     * @param AmoContactChangedEvent $event
     */
    public function processContactAmoChanged(AmoContactChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateIntercomContactByUser($event->getUser());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_CONTACT_AMO_CHANGED));
    }

    /**
     * @param SalonContactsChangedEvent $event
     */
    public function processCompanyContactInfoChanged(SalonContactsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeInfoSettings($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_INFO_CHANGED));
    }

    /**
     * @param SalonSettingsCreatedEvent $event
     * @throws HasUserMessageException
     * @throws InvalidArgumentException
     */
    public function processCompanySettingsCreated(SalonSettingsCreatedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->createIntercomRelatedEntities(
            $event->getSalon(),
            $event->getUser(),
            $event->getUserRegisterTargetMetricsDto()
        );
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_SETTINGS_CREATED));
    }

    /**
     * @param SalonBaseSettingsChangedEvent $event
     */
    public function processCompanyMainSettingsChanged(SalonBaseSettingsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeMainSettings($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_MAIN_SETTINGS_CHANGED));
    }

    /**
     * @param LicenseUpdatedEvent $event
     */
    public function processCompanyLicenseChanged(LicenseUpdatedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeLicenseSettings($event->getLicenseId());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_LICENSE_CHANGED));
    }

    public function processCompanyLicensePaid(LicensePayedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnLicensePaid($event->getLicense());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_LICENSE_CHANGED));
    }

    public function processCompanyLicenseActivated(LicenseActivatedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeLicenseActivity($event->getLicense());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_LICENSE_ACTIVATED));
    }

    public function processCompanyActivated(SalonActivatedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeSalonActivity($event->getSalon());
        $this->intercomService->updateContactsOnChangeSalonActivity($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_ACTIVATED));
    }

    public function processCompanyDeactivated(SalonDeactivatedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeSalonActivity($event->getSalon());
        $this->intercomService->updateContactsOnChangeSalonActivity($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_DEACTIVATED));
    }

    /**
     * @param AmoCallBackChangeLeadManagerEvent $event
     */
    public function processCompanyManagerChanged(AmoCallBackChangeLeadManagerEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeManager($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_MANAGER_CHANGED));
    }

    /**
     * @param AmoCallBackChangeConsultingStatusEvent $event
     */
    public function processCompanyStatusConsultingChanged(AmoCallBackChangeConsultingStatusEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeConsultingStatus($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_CONSULTING_CHANGED));
    }

    /**
     * @param SalonPlanSettingsChangedEvent $event
     */
    public function processCompanyTariffChanged(SalonPlanSettingsChangedEvent $event): void
    {
        $this->logEventName($event);
        $this->intercomService->updateCompanyOnChangeTariffSettings($event->getSalon());
        Metrics::increment(IntercomMetric::createEventMetric(IntercomMetric::METRIC_EVENT_COMPANY_TARIFF_CHANGED));
    }
}
