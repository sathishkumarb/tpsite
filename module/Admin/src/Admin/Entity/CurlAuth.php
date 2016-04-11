<?php

namespace Admin\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CurlAuth
 *
 * @ORM\Table(name="curl_auth")
 * @ORM\Entity(repositoryClass="CurlAuthRepository")
 * 
 */
class CurlAuth {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * 
     * @var type string
     * @ORM\Column(name="token", type="string", nullable=false)
     */
    private $token;

    /**
     *
     * @var type string
     * @ORM\Column(name="start_time", type="string", nullable=false)
     */
    private $startTime;

    /**
     *
     * @var type string
     * @ORM\Column(name="end_time", type="string", nullable=false)
     */
    private $endTime;

    /**
     * get Id 
     * @return type integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * get Toekn
     * @return type string
     */
    public function getToekn() {
        return $this->token;
    }

    /**
     * set Token
     * @param type $token
     * @return \Admin\Entity\CurlAuth
     */
    public function setToken($token) {
        $this->token = $token;
        return $this;
    }

    /**
     * get StartTime
     * @return type datetime
     */
    public function getStartTime() {
        return $this->startTime;
    }

    /**
     * set StartTime
     * @param type $startTime
     * @return \Admin\Entity\CurlAuth
     */
    public function setStartTime($startTime) {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * getEndTime
     * @return type datetime
     */
    public function getEndTime() {
        return $this->endTime;
    }

    /**
     * set EndTime
     * @param type $endTime
     * @return \Admin\Entity\CurlAuth
     */
    public function setEndTime($endTime) {
        $this->endTime = $endTime;
        return $this;
    }

}
