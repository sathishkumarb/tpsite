<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cms
 *
 * @ORM\Table(name="cms")
 * @ORM\Entity(repositoryClass="CmsRepository")
 */
class Cms
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
     * @ORM\Column(name="page_title", type="string", length=255, nullable=false)
     */
    private $pageTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", length=65535, nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="keywords", type="text", length=65535, nullable=false)
     */
    private $keywords;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_tag", type="text", length=65535, nullable=false)
     */
    private $metaTag;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_desc", type="text", length=65535, nullable=false)
     */
    private $metaDesc;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="smallint", nullable=true)
     */
    private $status = '0';

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
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set pageTitle
     *
     * @param string $pageTitle
     *
     * @return Cms
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * Get pageTitle
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Cms
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
     * Set keywords
     *
     * @param string $keywords
     *
     * @return Cms
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;

        return $this;
    }

    /**
     * Get keywords
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set metaTag
     *
     * @param string $metaTag
     *
     * @return Cms
     */
    public function setMetaTag($metaTag)
    {
        $this->metaTag = $metaTag;

        return $this;
    }

    /**
     * Get metaTag
     *
     * @return string
     */
    public function getMetaTag()
    {
        return $this->metaTag;
    }

    /**
     * Set metaDesc
     *
     * @param string $metaDesc
     *
     * @return Cms
     */
    public function setMetaDesc($metaDesc)
    {
        $this->metaDesc = $metaDesc;

        return $this;
    }

    /**
     * Get metaDesc
     *
     * @return string
     */
    public function getMetaDesc()
    {
        return $this->metaDesc;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Cms
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

    /**
     * Set createdDate
     *
     * @param \DateTime $createdDate
     *
     * @return Cms
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
     * @return Cms
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
