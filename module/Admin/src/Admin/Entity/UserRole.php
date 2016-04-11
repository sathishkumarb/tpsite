<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserRole
 *
 * @ORM\Table(name="user_role", uniqueConstraints={@ORM\UniqueConstraint(name="id_unique", columns={"id"})})
 * @ORM\Entity(repositoryClass="RolesRepository")
 */
class UserRole
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
     * @ORM\Column(name="role_name", type="string", length=60, nullable=false)
     */
    private $roleName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false)
     */
    private $createdDate;
	
	/**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_date", type="datetime", nullable=false)
     */
    private $updatedDate;
	
	/**
     * @var \integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;



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
     * Set roleName
     *
     * @param string $roleName
     *
     * @return roleName
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * Get roleName
     *
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }


    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return createdDate
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
     * @return updatedDate
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
     * Set status
     *
     * @param integer $status
     *
     * @return status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
	
	/**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
