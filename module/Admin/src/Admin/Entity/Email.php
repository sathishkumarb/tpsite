<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cms
 *
 * @ORM\Table(name="email_templates")
 * @ORM\Entity(repositoryClass="EmailRepository")
 */
class Email
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
     * @ORM\Column(name="subject", type="string", length=255, nullable=false)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="keydata", type="string", length=255, nullable=false)
     */
    private $keydata;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_active", type="integer", nullable=false)
     */
    private $isActive;

    /**
     * @var datetime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false)
     */
    private $createdDate;

    /**
     * @var datetime
     *
     * @ORM\Column(name="modified_date", type="datetime", nullable=true)
     */
    private $modifiedDate;

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
     * Set subject
     *
     * @param string $subject
     *
     * @return Email
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getsubject()
    {
        return $this->subject;
    }

    /**
     * Set content
     *
     * @param string $value
     *
     * @return Email
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set keydata
     *
     * @param string $keydata
     *
     * @return Email
     */
    public function setKeydata($keydata)
    {
        $this->keydata = $keydata;
        return $this;
    }

    /**
     * Get keydata
     *
     * @return string
     */
    public function getKeydata()
    {
        return $this->keydata;
    }

    /**
     * Set isActive
     *
     * @param string $isActive
     *
     * @return Email
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }
    
    /**
     * Get isActive
     *     
     * @return isActive
     */
    public function getIsActive()
    {
        return $this->isActive;
        
    }   

    
    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Email
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
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     *
     * @return Email
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    /**
     * Get modifiedDate
     *
     * @return \DateTime
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }
}
