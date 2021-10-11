<?php

declare(strict_types=1);

namespace More\Amo\Events;

use CSalon;
use More\Amo\Data\AmoContact;
use More\EventDispatcher\AbstractEvent;
use More\User\Interfaces\UserInterface;

class AmoContactLeadsResolvedEvent extends AbstractEvent
{
    public const EVENT_NAME = 'amo.contact.leads.changed';

    private AmoContact $amoContact;
    private UserInterface $user;
    private CSalon $salon;

    public function __construct(AmoContact $amoContact, UserInterface $user, Csalon $salon)
    {
        $this->amoContact = $amoContact;
        $this->user = $user;
        $this->salon = $salon;
    }

    public function getAmoContact(): AmoContact
    {
        return $this->amoContact;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function getSalon(): CSalon
    {
        return $this->salon;
    }
}
