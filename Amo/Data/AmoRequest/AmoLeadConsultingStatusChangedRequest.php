<?php

namespace More\Amo\Data\AmoRequest;

use More\Amo\Data\AmoConsultingStatus;
use More\Amo\Data\AmoResponse\AmoEntityContainer;
use More\Amo\Exceptions\AmoBadParamsException;

class AmoLeadConsultingStatusChangedRequest
{
    private AmoConsultingStatus $consultingStatus;

    public function __construct(AmoConsultingStatus $consultingStatus)
    {
        $this->consultingStatus = $consultingStatus;
    }

    /**
     * @param array $data
     * @return AmoLeadConsultingStatusChangedRequest
     * @throws AmoBadParamsException
     */
    public static function createFromArray(array $data): AmoLeadConsultingStatusChangedRequest
    {
        $statusEntityArray = $data['leads']['update'][0] ?? null;

        if (! $statusEntityArray) {
            throw new AmoBadParamsException('Bad request format data');
        }

        return new self(AmoConsultingStatus::createFromAmoEntityContainer(new AmoEntityContainer($statusEntityArray)));
    }

    /**
     * @return AmoConsultingStatus
     */
    public function getConsultingStatus(): AmoConsultingStatus
    {
        return $this->consultingStatus;
    }
}
