<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Event\Service;

use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;

/**
 * Event management service.
 */
class EventManager
{
    private $dataProvider;
    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Event::class);
    }
    
    /**
     * Create an event
     *
     * @param Event $event The event to create
     *
     * @return Event|null The event created or null if error.
     */
    public function createEvent(Event $event)
    {
        $response = $this->dataProvider->post($event);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Get all events which end date happens after a certain date.
     * @param \DateTimeInterface    $endDateIsAfter     The date after which the end date of events are taken account (usually the current date)
     * @param string                $orderBy            Property on which order the results (id or fromDate)
     * @param string                $order              Order type (asc or desc)
     * @param int                   $limit              The max number of results
     * @return array|null The events found or null if not found.
     */
    public function getEvents(?\DateTimeInterface $endDateIsAfter, string $orderBy="fromDate", string $order="asc", int $limit=null)
    {
        $params=[];
        if ($endDateIsAfter) {
            $params['toDate[after]'] = $endDateIsAfter->format('Y-m-d');
        } else {
            $params['toDate[after]'] = new \DateTime();
        }
        if ($orderBy == "fromDate") {
            $params['order[fromDate]'] = $order;
        }
        if ($orderBy == "id") {
            $params['order[id]'] = $order;
        }
        if ($limit) {
            $params['perPage'] = $limit;
        }
        $response = $this->dataProvider->getCollection($params);
        return $response->getValue();
    }
    
    /**
     * Get an event
     *
     * @param int $id
     * @return Event|null The event found or null if not found.
     */
    public function getEvent(int $id)
    {
        $response = $this->dataProvider->getItem($id);

        return $response->getValue();
    }
    
    /**
     * Update an event
     *
     * @param Event $event The event to update
     *
     * @return Event|null The event updated or null if error.
     */
    public function updateEvent(Event $event)
    {
        $response = $this->dataProvider->put($event);
        return $response->getValue();
    }
    
    /**
     * Delete an event
     *
     * @param int $id The id of the event to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteEvent(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }
}
