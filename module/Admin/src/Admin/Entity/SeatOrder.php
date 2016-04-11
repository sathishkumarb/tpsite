<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SeatOrder
 *
 * @ORM\Table(name="seat_order", indexes={@ORM\Index(name="FK_seat_order_user_booking", columns={"booking_id"}), @ORM\Index(name="FK_seat_order_eventseatid", columns={"event_seat_id"}), @ORM\Index(name="FK_seat_order_billing_address_id", columns={"billing_address_id"})})
 * @ORM\Entity
 */
class SeatOrder
{
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
     * @ORM\Column(name="seat_no", type="integer", nullable=false)
     */
    private $seatNo;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=0, nullable=false)
     */
    private $price;

    /**
     * @var string
     *
     * @ORM\Column(name="entrance", type="string", length=100, nullable=false)
     */
    private $entrance;

    /**
     * @var string
     *
     * @ORM\Column(name="ticket_type", type="string", length=100, nullable=false)
     */
    private $ticketType;

    /**
     * @var string
     *
     * @ORM\Column(name="redeem_on", type="string", length=100, nullable=false)
     */
    private $redeemOn;

    /**
     * @var string
     *
     * @ORM\Column(name="bar_code_number", type="string", length=20, nullable=false)
     */
    private $barCodeNumber;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false)
     */
    private $createdDate;

    

    /**
     * @var \Admin\Entity\EventSeat
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\EventSeat")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_seat_id", referencedColumnName="id")
     * })
     */
    private $eventSeat;

    /**
     * @var \Admin\Entity\UserBooking
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\UserBooking")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="booking_id", referencedColumnName="id")
     * })
     */
    private $booking;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set seatNo
     *
     * @param integer $seatNo
     *
     * @return SeatOrder
     */
    public function setSeatNo($seatNo)
    {
        $this->seatNo = $seatNo;

        return $this;
    }

    /**
     * Get seatNo
     *
     * @return integer
     */
    public function getSeatNo()
    {
        return $this->seatNo;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return SeatOrder
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set entrance
     *
     * @param string $entrance
     *
     * @return SeatOrder
     */
    public function setEntrance($entrance)
    {
        $this->entrance = $entrance;

        return $this;
    }

    /**
     * Get entrance
     *
     * @return string
     */
    public function getEntrance()
    {
        return $this->entrance;
    }

    /**
     * Set ticketType
     *
     * @param string $ticketType
     *
     * @return SeatOrder
     */
    public function setTicketType($ticketType)
    {
        $this->ticketType = $ticketType;

        return $this;
    }

    /**
     * Get ticketType
     *
     * @return string
     */
    public function getTicketType()
    {
        return $this->ticketType;
    }

    /**
     * Set redeemOn
     *
     * @param string $redeemOn
     *
     * @return SeatOrder
     */
    public function setRedeemOn($redeemOn)
    {
        $this->redeemOn = $redeemOn;

        return $this;
    }

    /**
     * Get redeemOn
     *
     * @return string
     */
    public function getRedeemOn()
    {
        return $this->redeemOn;
    }

    /**
     * Set barCodeNumber
     *
     * @param string $barCodeNumber
     *
     * @return SeatOrder
     */
    public function setBarCodeNumber($barCodeNumber)
    {
        $this->barCodeNumber = $barCodeNumber;

        return $this;
    }

    /**
     * Get barCodeNumber
     *
     * @return string
     */
    public function getBarCodeNumber()
    {
        return $this->barCodeNumber;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return SeatOrder
     */
    public function setCreatedDate($createdDate)
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    /**
     * Get createdDate
     *
     * @return \DateTime
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    

    /**
     * Set eventSeat
     *
     * @param \Admin\Entity\EventSeat $eventSeat
     *
     * @return SeatOrder
     */
    public function setEventSeat(\Admin\Entity\EventSeat $eventSeat = null)
    {
        $this->eventSeat = $eventSeat;

        return $this;
    }

    /**
     * Get eventSeat
     *
     * @return \Admin\Entity\EventSeat
     */
    public function getEventSeat()
    {
        return $this->eventSeat;
    }

    /**
     * Set booking
     *
     * @param \Admin\Entity\UserBooking $booking
     *
     * @return SeatOrder
     */
    public function setBooking(\Admin\Entity\UserBooking $booking = null)
    {
        $this->booking = $booking;

        return $this;
    }

    /**
     * Get booking
     *
     * @return \Admin\Entity\UserBooking
     */
    public function getBooking()
    {
        return $this->booking;
    }
}
