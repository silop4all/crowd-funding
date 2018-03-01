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
 * @ ORM\Entity
 * @ UniqueEntity(fields="email", message="Email already taken")
 * @ UniqueEntity(fields="username", message="Username already taken")
 */
class Category {
//implements UserInterface {
    /**
     * @ ORM\Id
     * @ ORM\Column(type="integer")
     * @ ORM\GeneratedValue(strategy="AUTO")
     */
    //private $id;

    /**
     * @ ORM\Column(type="string", length=255, unique=true)
     * @ Assert\NotBlank()
     * @ Assert\Email()
     */
    private $title;

    // other properties and methods

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
        return $this;
    }

}
