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
class Project {
    //put your code here
    public $name;
    public $description;
    public $content;
    public $amount;
    public $user;
    public $period;

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getContent() {
        return $this->content;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getUser() {
        return $this->user;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setDescription($description) {
        $this->description = $description;
        return $this;
    }

    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
        return $this;
    }

    public function setUser($user) {
        $this->user = $user;
        return $this;
    }

    public function getPeriod() {
        return $this->period;
    }

    public function setPeriod($period) {
        $this->period = $period;
        return $this;
    }

}
