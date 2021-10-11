<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Subscribers;

use More\Integration\Intercom\Services\IntercomService;
use More\Registration\Events\Wizard\WizardFirstStepCompleteEvent;
use More\Registration\Events\Wizard\WizardLastStepCompleteEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IntercomWizardStepsSubscriber implements EventSubscriberInterface
{
    private IntercomService $intercomService;

    /**
     * IntercomWizardStepsSubscriber constructor.
     * @param IntercomService $intercomService
     */
    public function __construct(IntercomService $intercomService)
    {
        $this->intercomService = $intercomService;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WizardFirstStepCompleteEvent::name() => 'processFirstStepComplete',
            WizardLastStepCompleteEvent::name()  => 'processLastStepComplete',
        ];
    }

    /**
     * @param WizardFirstStepCompleteEvent $event
     */
    public function processFirstStepComplete(WizardFirstStepCompleteEvent $event): void
    {
        $this->intercomService->updateCompanyOnWizardFirstStep($event->getSalon());
    }

    /**
     * @param WizardLastStepCompleteEvent $event
     */
    public function processLastStepComplete(WizardLastStepCompleteEvent $event): void
    {
        $this->intercomService->updateIntercomEntitiesOnWizardDone($event->getUser(), $event->getSalon());
    }
}
