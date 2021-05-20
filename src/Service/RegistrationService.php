<?php

namespace Mr\CventSdk\Service;

use Mr\Bootstrap\Service\BaseHttpService;
use Mr\CventSdk\Repository\Registration\AttendeeRepository;
use Mr\CventSdk\Repository\Registration\EventRepository;
use Mr\CventSdk\Model\Registration\Attendee;
use Mr\CventSdk\Model\Registration\Event;

class RegistrationService extends BaseHttpService
{
    /**
     * Returns Attendee by id
     *
     * @param $id
     * @return Attendee
     */
    public function getAttendee($id)
    {
        return $this->getRepository(AttendeeRepository::class)->get($id);
    }

    /**
     * Returns all Attendees matching filters
     *
     * @param array $filters
     * @return array
     */
    public function findAttendees(array $filters = [], array &$metadata = [])
    {
        return $this->getRepository(AttendeeRepository::class)->all($filters);
    }

    /**
     * Returns Event by id
     *
     * @param $id
     * @return Event
     */
    public function getEvent($id)
    {
        return $this->getRepository(EventRepository::class)->get($id);
    }

    /**
     * Returns all Attendees matching filters
     *
     * @param array $filters
     * @return array
     */
    public function findEvents(array $filters = [], array &$metadata = [])
    {
        return $this->getRepository(EventRepository::class)->all($filters, false, $metadata);
    }
}
