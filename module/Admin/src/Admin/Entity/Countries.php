<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Countries
 *
 * @ORM\Table(name="countries")
 * @ORM\Entity(repositoryClass="CountriesRepository")
 * 
 */
class Countries
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
     * @ORM\Column(name="country_name", type="string", length=50, nullable=false)
     */
    private $countryName;

    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=5, nullable=false)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_code", type="string", length=5, nullable=true)
     */
    private $phoneCode;

    /**
     * @var integer
     *
     * @ORM\Column(name="country_exist", type="smallint", nullable=true)
     */
    private $countryExist = '1';



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
     * Set countryName
     *
     * @param string $countryName
     *
     * @return Countries
     */
    public function setCountryName($countryName)
    {
        $this->countryName = $countryName;

        return $this;
    }

    /**
     * Get countryName
     *
     * @return string
     */
    public function getCountryName()
    {
        return $this->countryName;
    }

    /**
     * Set countryCode
     *
     * @param string $countryCode
     *
     * @return Countries
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set phoneCode
     *
     * @param string $phoneCode
     *
     * @return Countries
     */
    public function setPhoneCode($phoneCode)
    {
        $this->phoneCode = $phoneCode;

        return $this;
    }

    /**
     * Get phoneCode
     *
     * @return string
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    /**
     * Set countryExist
     *
     * @param integer $countryExist
     *
     * @return Countries
     */
    public function setCountryExist($countryExist)
    {
        $this->countryExist = $countryExist;

        return $this;
    }

    /**
     * Get countryExist
     *
     * @return integer
     */
    public function getCountryExist()
    {
        return $this->countryExist;
    }
}
