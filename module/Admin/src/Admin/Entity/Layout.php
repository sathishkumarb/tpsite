<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Layout
 *
 * @ORM\Table(name="layout")
  * @ORM\Entity(repositoryClass="LayoutRepository")
 */
class Layout
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
     * @ORM\Column(name="layout_name", type="string", length=100, nullable=false)
     */
    private $layoutName;

    /**
     * @var string
     *
     * @ORM\Column(name="layout_image", type="string", length=200, nullable=false)
     */
    private $layoutImage;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_deleted", type="smallint", nullable=false)
     */
    private $isDeleted = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=false)
     */
    private $createdDate = 'CURRENT_TIMESTAMP';



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
     * Set layoutName
     *
     * @param string $layoutName
     *
     * @return Layout
     */
    public function setLayoutName($layoutName)
    {
        $this->layoutName = $layoutName;

        return $this;
    }

    /**
     * Get layoutName
     *
     * @return string
     */
    public function getLayoutName()
    {
        return $this->layoutName;
    }

    /**
     * Set layoutImage
     *
     * @param string $layoutImage
     *
     * @return Layout
     */
    public function setLayoutImage($layoutImage)
    {
        $this->layoutImage = $layoutImage;

        return $this;
    }

    /**
     * Get layoutImage
     *
     * @return string
     */
    public function getLayoutImage()
    {
        return $this->layoutImage;
    }

    /**
     * Set isDeleted
     *
     * @param integer $isDeleted
     *
     * @return Layout
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return integer
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Layout
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
}
