<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserCardDetails
 *
 * @ORM\Table(name="user_card_details", indexes={@ORM\Index(name="FK_user_card_details_userid", columns={"user_id"})})
 * @ORM\Entity
 */
class UserCardDetails
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
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=25, nullable=true)
     */
    private $title;
    
    /**
     * @var string
     *
     * @ORM\Column(name="card_type", type="string", length=100, nullable=true)
     */
    private $cardType;

    /**
     * @var string
     *
     * @ORM\Column(name="card_no", type="string", length=50, nullable=true)
     */
    private $cardNo;

    /**
     * @var string
     *
     * @ORM\Column(name="card_expiry_month", type="string", length=50, nullable=true)
     */
    private $cardExpiryMonth;

    /**
     * @var string
     *
     * @ORM\Column(name="card_expiry_year", type="string", length=5, nullable=true)
     */
    private $cardExpiryYear;
    
    /**
     * @var string
     *
     * @ORM\Column(name="is_deleted", type="string", length=1, nullable=true)
     */
    private $isDeleted;
    
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_date", type="datetime", nullable=true)
     */
    private $updatedDate;

    /**
     * @var \Admin\Entity\Users
     *
     * @ORM\ManyToOne(targetEntity="Admin\Entity\Users")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;



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
     * Get title
     *
     * @return string
     */
    public function getTitle(){
        return $this->title;
    }
    
    /**
     * Set title
     *
     * @param string $title
     *
     * @return UserCardDetails
     */
    public function setTitle($title){
        $this->title = $title;
        return $this;
    }
    
    /**
     * Set cardType
     *
     * @param string $cardType
     *
     * @return UserCardDetails
     */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;

        return $this;
    }

    /**
     * Get cardType
     *
     * @return string
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * Set cardNo
     *
     * @param string $cardNo
     *
     * @return UserCardDetails
     */
    public function setCardNo($cardNo)
    {
        $this->cardNo = $cardNo;

        return $this;
    }

    /**
     * Get cardNo
     *
     * @return string
     */
    public function getCardNo()
    {
        return $this->cardNo;
    }

    /**
     * Set cardExpiryMonth
     *
     * @param string $cardExpiryMonth
     *
     * @return UserCardDetails
     */
    public function setCardExpiryMonth($cardExpiryMonth)
    {
        $this->cardExpiryMonth = $cardExpiryMonth;

        return $this;
    }

    /**
     * Get cardExpiryMonth
     *
     * @return string
     */
    public function getCardExpiryMonth()
    {
        return $this->cardExpiryMonth;
    }

    /**
     * Set cardExpiryYear
     *
     * @param string $cardExpiryYear
     *
     * @return UserCardDetails
     */
    public function setCardExpiryYear($cardExpiryYear)
    {
        $this->cardExpiryYear = $cardExpiryYear;

        return $this;
    }

    /**
     * Get cardExpiryYear
     *
     * @return string
     */
    public function getCardExpiryYear()
    {
        return $this->cardExpiryYear;
    }
    /**
     * Get isDeleted
     *
     * @return string
     */
    public function getIsDeleted(){
        return $this->isDeleted;
    }
    /**
     * set isDeleted
     *
     * @return string
     */
    public function setIsDeleted($isDeleted){
        $this->isDeleted = $isDeleted;
        return $this;
    }
    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return UserCardDetails
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
     * Set updatedDate
     *
     * @param \DateTime $updatedDate
     *
     * @return UserCardDetails
     */
    public function setUpdatedDate($updatedDate)
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    /**
     * Get updatedDate
     *
     * @return \DateTime
     */
    public function getUpdatedDate()
    {
        return $this->updatedDate;
    }

    /**
     * Set user
     *
     * @param \Admin\Entity\Users $user
     *
     * @return UserCardDetails
     */
    public function setUser(\Admin\Entity\Users $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Admin\Entity\Users
     */
    public function getUser()
    {
        return $this->user;
    }
}
