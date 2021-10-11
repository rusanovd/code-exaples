<?php

namespace More\Amo\Data\AmoRequest;

use More\Amo\Data\AmoResponse\AmoEntityContainer;
use More\Amo\Data\AmoResponsible;
use More\Amo\Exceptions\AmoBadParamsException;

class AmoLeadResponsibleUserChangedRequest
{
    private AmoResponsible $amoResponsible;

    public function __construct(AmoResponsible $amoResponsible)
    {
        $this->amoResponsible = $amoResponsible;
    }

    /**
     * @param array $data
     * @return AmoLeadResponsibleUserChangedRequest
     * @throws AmoBadParamsException
     */
    public static function createFromArray(array $data): AmoLeadResponsibleUserChangedRequest
    {
        $responsibleEntityArray = $data['leads']['responsible'][0] ?? null;

        if (! $responsibleEntityArray) {
            throw new AmoBadParamsException('Bad request format data');
        }

        return new self(AmoResponsible::createFromAmoEntityContainer(new AmoEntityContainer($responsibleEntityArray)));
    }

    /**
     * @return AmoResponsible
     */
    public function getAmoResponsible(): AmoResponsible
    {
        return $this->amoResponsible;
    }
}
