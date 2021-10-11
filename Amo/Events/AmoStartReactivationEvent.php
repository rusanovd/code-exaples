<?php

declare(strict_types=1);

namespace More\Amo\Events;

use CSalon;
use More\Amo\Data\Dto\AmoLeadStartReactivationDto;
use More\EventDispatcher\AbstractEvent;

class AmoStartReactivationEvent extends AbstractEvent
{
    public const EVENT_NAME = 'amo.lead.reactivation.started';

    private CSalon $salon;
    private AmoLeadStartReactivationDto $amoLeadStartReactivationDto;

    /**
     * AmoStartReactivationEvent constructor.
     * @param CSalon $salon
     * @param AmoLeadStartReactivationDto $amoLeadStartReactivationDto
     */
    public function __construct(CSalon $salon, AmoLeadStartReactivationDto $amoLeadStartReactivationDto)
    {
        $this->salon = $salon;
        $this->amoLeadStartReactivationDto = $amoLeadStartReactivationDto;
    }

    /**
     * @return CSalon
     */
    public function getSalon(): CSalon
    {
        return $this->salon;
    }

    /**
     * @return AmoLeadStartReactivationDto
     */
    public function getAmoLeadStartReactivationDto(): AmoLeadStartReactivationDto
    {
        return $this->amoLeadStartReactivationDto;
    }
}
