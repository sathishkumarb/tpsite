<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventDisplayCities
 *
 * @ORM\Table(name="event_display_cities",indexes={@ORM\Index(name="FK_event_display_cities", columns={"event_id","city_id"})})
 * @ORM\Entity
*/
class EventDisplayCities {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="city_id", type="integer", length=11, nullable=false)
     */
    private $cityId;

    /**
     *
     * @var type integer
     * @ORM\Column(name="event_id", type="integer", length=11, nullable=false)
     */
    private $eventId;

     /**
     * @var \Admin\Entity\Event
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

     /**
     * @var \Admin\Entity\City
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * })
     */
    private $evCity;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set cityId
     *
     * @param integer $cityId
     *
     * @return City
     */
    public function setCityId($cityId) {
        $this->cityId = $cityId;

        return $this;
    }

    /**
     * Get cityId
     *
     * @return integer 
     */
    public function getCityId() {
        return $this->cityId;
    }

    /**
     * set eventId
     *
     * @param integer $eventId
     *
     * @return Event
     */
    public function setEventId($eventId) {
        $this->eventId = $eventId;
        return $this;
    }

    /**
     * Get eventId
     *
     * @return integer
     */
    public function getEventId() {
        return $this->eventId;
    }

    /**
     * Set event
     *
     * @param \Admin\Entity\Event $event
     *
     * @return EventDisplayCities
     */
    public function setEvent(\Admin\Entity\Event $event = null) {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event
     *
     * @return \Admin\Entity\Event
     */
    public function getEvent() {
        return $this->event;
    }

    /**
     * Set evCity
     *
     * @param \Admin\Entity\City $evCity
     *
     * @return Event
     */
    public function setEventCity(\Admin\Entity\City $evCity = null) {
        $this->evCity = $evCity;

        return $this;
    }

    /**
     * Get eventCity
     *
     * @return \Admin\Entity\City
     */
    public function getEventCity() {
        return $this->evCity;
    }

}
