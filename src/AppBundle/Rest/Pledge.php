<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace AppBundle\Rest;

/**
 * Description of Offer
 *
 * @author Panagiotis Minos
 */
class Pledge {
    //put your code here
    public $project;
    public $user;
    public $amount;
    public $paymentID;
    
    public function getProject() {
        return $this->project;
    }

    public function getUser() {
        return $this->user;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    public function getPaymentID() {
        return $this->paymentID;
    }

    public function setPaymentID($paymentID) {
        $this->paymentID = $paymentID;
        return $this;
    }



}
