<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Project
 *
 * @author Panagiotis Minos
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProjectRepository")
 * @ORM\Table(name="project")
 */
class Project {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *  @ORM\Column(type="datetime", name="ctime") 
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", name="etime") 
     */
    private $etime;
    /**
     * @ORM\Column(type="boolean", name="expired") 
     */
    private $expired;
    /**
     * @ORM\Column(type="string", length=255, unique=false, name="name")
     * @Assert\NotBlank()
     * @ Assert\Email()
     */
    private $title;

    /**
     * @ORM\Column(type="text", length=65535, unique=false, name="description")
     * @Assert\NotBlank()
     * @Assert\Length(max=65535)
     */
    private $summary;

    /**
     * @ORM\Column(type="text", length=65535, unique=false, name="content")
     * @Assert\NotBlank()
     * @Assert\Length(max=65535)
     */
    private $content;

    /**
     * The below length depends on the "algorithm" you use for encoding
     * the password, but this works well with bcrypt.
     *
     * @ORM\Column(type="string", length=64)
     */
    public $amount;

    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    public $user;

    /**
     * 
     * 
     *
     * @ORM\Column(type="integer")
     */
    public $period;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Project
     */
    public function setTitle($title) {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set summary
     *
     * @param string $summary
     *
     * @return Project
     */
    public function setSummary($summary) {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary() {
        return $this->summary;
    }

    /**
     * Set content
     *
     * @param string $content
     *
     * @return Project
     */
    public function setContent($content) {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Project
     */
    public function setAmount($amount) {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Project
     */
    public function setUser(\AppBundle\Entity\User $user = null) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set period
     *
     * @param integer $period
     *
     * @return Project
     */
    public function setPeriod($period) {
        $this->period = $period;

        return $this;
    }

    /**
     * Get period
     *
     * @return integer
     */
    public function getPeriod() {
        return $this->period;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Project
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set etime
     *
     * @param \DateTime $etime
     *
     * @return Project
     */
    public function setEtime($etime) {
        $this->etime = $etime;

        return $this;
    }

    /**
     * Get etime
     *
     * @return \DateTime
     */
    public function getEtime() {
        return $this->etime;
    }


    /**
     * Set expired
     *
     * @param boolean $expired
     *
     * @return Project
     */
    public function setExpired($expired)
    {
        $this->expired = $expired;

        return $this;
    }

    /**
     * Get expired
     *
     * @return boolean
     */
    public function getExpired()
    {
        return $this->expired;
    }
}
