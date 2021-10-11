<?php

declare(strict_types=1);

namespace More\Amo\Events;

use CSalon;
use More\Amo\Data\AmoResponsible;
use More\EventDispatcher\AbstractEvent;

class AmoCallBackChangeLeadManagerEvent extends AbstractEvent
{
    public const EVENT_NAME = 'amo.callback.manager.changed';

    private CSalon $salon;
    private AmoResponsible $amoResponsible;

    /**
     * AmoCallBackChangeLeadManagerEvent constructor.
     * @param CSalon $salon
     * @param AmoResponsible $amoResponsible
     */
    public function __construct(CSalon $salon, AmoResponsible $amoResponsible)
    {
        $this->salon = $salon;
        $this->amoResponsible = $amoResponsible;
    }

    /**
     * @return CSalon
     */
    public function getSalon(): CSalon
    {
        return $this->salon;
    }

    /**
     * @return AmoResponsible
     */
    public function getAmoResponsible(): AmoResponsible
    {
        return $this->amoResponsible;
    }
}
