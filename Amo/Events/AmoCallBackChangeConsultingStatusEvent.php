<?php

declare(strict_types=1);

namespace More\Amo\Events;

use CSalon;
use More\EventDispatcher\AbstractEvent;

class AmoCallBackChangeConsultingStatusEvent extends AbstractEvent
{
    public const EVENT_NAME = 'amo.callback.status.consulting.changed';

    private CSalon $salon;

    /**
     * AmoCallBackChangeConsultingStatusEvent constructor.
     * @param CSalon $salon
     */
    public function __construct(CSalon $salon)
    {
        $this->salon = $salon;
    }

    /**
     * @return CSalon
     */
    public function getSalon(): CSalon
    {
        return $this->salon;
    }
}
