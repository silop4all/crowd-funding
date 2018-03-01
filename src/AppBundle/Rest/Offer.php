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
class Offer {
    //put your code here
    public $projectRequest;
    public $user;
    public $amount;
    
    public function getProjectRequest() {
        return $this->projectRequest;
    }

    public function getUser() {
        return $this->user;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setProjectRequest($projectRequest) {
        $this->projectRequest = $projectRequest;
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


}
