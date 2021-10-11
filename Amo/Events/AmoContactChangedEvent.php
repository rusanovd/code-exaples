<?php

declare(strict_types=1);

namespace More\Amo\Events;

use More\EventDispatcher\AbstractEvent;
use More\User\Interfaces\UserInterface;

class AmoContactChangedEvent extends AbstractEvent
{
    public const EVENT_NAME = 'amo.contact.changed';

    private UserInterface $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
