<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * City
 *
 * @ORM\Table(name="city", uniqueConstraints={@ORM\UniqueConstraint(name="id_unique", columns={"id"})}, indexes={@ORM\Index(name="fk_county_state", columns={"country_id"})})
 * @ORM\Entity
 */
class City
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
     * @ORM\Column(name="country_id", type="integer", nullable=false)
     */
    private $countryId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="city_name", type="string", length=50, nullable=false)
     */
    private $cityName = 'new county';

    /**
     * @var integer
     *
     * @ORM\Column(name="supported", type="smallint", nullable=false)
     */
    private $supported = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_updated", type="datetime", nullable=false)
     */
    private $lastUpdated = 'CURRENT_TIMESTAMP';



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
     * Set countryId
     *
     * @param integer $countryId
     *
     * @return City
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;

        return $this;
    }

    /**
     * Get countryId
     *
     * @return integer
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * Set cityName
     *
     * @param string $cityName
     *
     * @return City
     */
    public function setCityName($cityName)
    {
        $this->cityName = $cityName;

        return $this;
    }

    /**
     * Get cityName
     *
     * @return string
     */
    public function getCityName()
    {
        return $this->cityName;
    }

    /**
     * Set supported
     *
     * @param integer $supported
     *
     * @return City
     */
    public function setSupported($supported)
    {
        $this->supported = $supported;

        return $this;
    }

    /**
     * Get supported
     *
     * @return integer
     */
    public function getSupported()
    {
        return $this->supported;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     *
     * @return City
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }
}
