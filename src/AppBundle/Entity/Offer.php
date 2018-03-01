<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Entity;

/**
 * Description of Offer
 *
 * @author Panagiotis Minos
 */
class Offer {

    private $amount;
    private $paymentID;

    public function getAmount() {
        return $this->amount;
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
