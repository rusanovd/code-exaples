<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Data\Response;

use More\Integration\Intercom\Data\IntercomCompany;
use More\Integration\Intercom\Data\IntercomContact;

class IntercomEntityFactory
{
    private IntercomContact $intercomContact;
    private IntercomCompany $intercomCompany;

    /**
     * IntercomEntityFactory constructor.
     * @param IntercomContact $intercomContact
     * @param IntercomCompany $intercomCompany
     */
    public function __construct(IntercomContact $intercomContact, IntercomCompany $intercomCompany)
    {
        $this->intercomContact = $intercomContact;
        $this->intercomCompany = $intercomCompany;
    }

    /**
     * @param \stdClass|array $responseObject
     * @return array
     */
    private function getResponseArray($responseObject): array
    {
        $_arr = is_object($responseObject) ? get_object_vars($responseObject) : $responseObject;
        foreach ($_arr as $key => $val) {
            $val = (is_array($val) || is_object($val)) ? $this->getResponseArray($val) : $val;
            $arr[$key] = $val;
        }

        return $arr ?? [];
    }

    /**
     * @param \stdClass $responseObject
     * @return IntercomEntityContainer
     */
    private function getResponseEntityContainer(\stdClass $responseObject): IntercomEntityContainer
    {
        return new IntercomEntityContainer($this->getResponseArray($responseObject));
    }

    /**
     * @param \stdClass $response
     * @return IntercomContact
     */
    public function getContact(\stdClass $response): IntercomContact
    {
        return $this->intercomContact->createFromIntercomEntityContainer($this->getResponseEntityContainer($response));
    }

    /**
     * @param \stdClass $response
     * @return IntercomCompany
     */
    public function getCompany(\stdClass $response): IntercomCompany
    {
        return $this->intercomCompany->createFromIntercomEntityContainer($this->getResponseEntityContainer($response));
    }
}
