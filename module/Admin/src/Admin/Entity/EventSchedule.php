<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EventSchedule
 *
 * @ORM\Table(name="event_schedule", indexes={@ORM\Index(name="FK_event_schedule", columns={"event_id"})})
 * @ORM\Entity(repositoryClass="EventScheduleRepository")
 */
class EventSchedule {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     */
    private $eventId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="event_date", type="date", nullable=false)
     */
    private $eventDate;

    /**
     * @var time
     *
     * @ORM\Column(name="event_time", type="time", nullable=false)
     */
    private $eventTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false)
     */
    private $createdDate;

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
     * @var integer
     *
     * @ORM\Column(name="is_deleted", type="smallint", nullable=false)
     */
    private $isDeleted;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set eventDate
     *
     * @param \DateTime $eventDate
     *
     * @return EventSchedule
     */
    public function setEventDate($eventDate) {
        $this->eventDate = $eventDate;

        return $this;
    }

    /**
     * Get eventDate
     *
     * @return \DateTime
     */
    public function getEventDate() {
        return $this->eventDate;
    }

    /**
     * get EventId
     * @return type
     */
    public function getEventId() {
        return $this->eventId;
    }

    /**
     * 
     * @param type $eventId
     * @return \Admin\Entity\EventSchedule
     */
    public function setEventId($eventId) {
        $this->eventId = $eventId;

        return $this;
    }

    /**
     * Set eventTime
     *
     * @param string $eventTime
     *
     * @return EventSchedule
     */
    public function setEventTime($eventTime) {
        $this->eventTime = $eventTime;

        return $this;
    }

    /**
     * Get eventTime
     *
     * @return string
     */
    public function getEventTime() {
        return $this->eventTime;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return EventSchedule
     */
    public function setCreatedDate($createdDate) {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate() {
        return $this->createdDate;
    }

    /**
     * Set event
     *
     * @param \Admin\Entity\Event $event
     *
     * @return EventSchedule
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

    /** Get IsDeleted
     * 
     * @return smallint
     * @author Aditya
     */
    public function getIsDeleted() {
        return $this->isDeleted;
    }

    /**
     * Set isDisabled
     * @param type $isDeleted
     * @return \Admin\Entity\EventSchedule
     * $author Aditya
     */
    public function setIsDeleted($isDeleted) {
        $this->isDeleted = $isDeleted;
        return $this;
    }

}
