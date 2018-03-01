<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of User
 *
 * @author Panagiotis Minos
 */
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="bid")
 */
class ProjectOffer {
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=64)
     * @ Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=64)
     * @ Assert\NotBlank()
     * @ Assert\Length(max=65535)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=64)
     * @ Assert\NotBlank()
     * @ Assert\Length(max=65535)
     */
    private $amount;

    /**
     *
     * @ORM\Column(type="string", length=64)
     */
    public $currency;

    /**
     *
     * @ORM\Column(type="string", length=64, name="paymentID")
     */
    public $paymentID;
    
        /**
     *
     * @ORM\Column(type="string", length=64, name="payerID")
     */
    public $payerID;
    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     */
    public $user;

    /**
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Project")
     */
    public $project;
    

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
     * Set name
     *
     * @param string $name
     *
     * @return ProposalOffer
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ProposalOffer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return ProposalOffer
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return ProposalOffer
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return ProposalOffer
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set proposal
     *
     * @param \AppBundle\Entity\Proposal $proposal
     *
     * @return ProposalOffer
     */
    public function setProposal(\AppBundle\Entity\Proposal $proposal = null)
    {
        $this->proposal = $proposal;

        return $this;
    }

    /**
     * Get proposal
     *
     * @return \AppBundle\Entity\Proposal
     */
    public function getProposal()
    {
        return $this->proposal;
    }

    /**
     * Set paymentID
     *
     * @param string $paymentID
     *
     * @return ProjectOffer
     */
    public function setPaymentID($paymentID)
    {
        $this->paymentID = $paymentID;

        return $this;
    }

    /**
     * Get paymentID
     *
     * @return string
     */
    public function getPaymentID()
    {
        return $this->paymentID;
    }

    /**
     * Set payerID
     *
     * @param string $payerID
     *
     * @return ProjectOffer
     */
    public function setPayerID($payerID)
    {
        $this->payerID = $payerID;

        return $this;
    }

    /**
     * Get payerID
     *
     * @return string
     */
    public function getPayerID()
    {
        return $this->payerID;
    }

    /**
     * Set project
     *
     * @param \AppBundle\Entity\Project $project
     *
     * @return ProjectOffer
     */
    public function setProject(\AppBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \AppBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
