<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categories
 *
 * @ORM\Table(name="categories")
 * @ORM\Entity(repositoryClass="CategoriesRepository")
 */
class Categories {

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
     * @ORM\Column(name="category_name", type="string", length=100, nullable=false)
     */
    private $categoryName;

    /**
     * @var string
     *
     * @ORM\Column(name="icon", type="string", length=100, nullable=false)
     */
    private $icon;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status;

    /**
     * @var integer
     *
     * @ORM\Column(name="is_deleted", type="smallint", nullable=false)
     */
    private $isDeleted = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_date", type="datetime", nullable=true)
     */
    private $createdDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified_date", type="datetime", nullable=true)
     */
    private $modifiedDate;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set categoryName
     *
     * @param string $categoryName
     *
     * @return Categories
     */
    public function setCategoryName($categoryName) {
        $this->categoryName = $categoryName;

        return $this;
    }

    /**
     * Get categoryName
     *
     * @return string
     */
    public function getCategoryName() {
        return $this->categoryName;
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return Categories
     */
    public function setIcon($icon) {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon() {
        return $this->icon;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Categories
     */
    public function setStatus($status) {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Set isDeleted
     *
     * @param integer $isDeleted
     *
     * @return Categories
     */
    public function setIsDeleted($isDeleted) {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return integer
     */
    public function getIsDeleted() {
        return $this->isDeleted;
    }

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Categories
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
     * Set modifiedDate
     *
     * @param \DateTime $modifiedDate
     *
     * @return Categories
     */
    public function setModifiedDate($modifiedDate) {
        $this->modifiedDate = $modifiedDate;

        return $this;
    }

    /**
     * Get modifiedDate
     *
     * @return \DateTime
     */
    public function getModifiedDate() {
        return $this->modifiedDate;
    }

}
