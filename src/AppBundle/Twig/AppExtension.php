<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AppExtension
 *
 * @author Panagiotis Minos
 */
// src/AppBundle/Twig/AppExtension.php

namespace AppBundle\Twig;

use \AppBundle\IAMClient;

class AppExtension extends \Twig_Extension {

    private $session;

    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session) {
        $this->session = $session;
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('logged', array($this, 'logged')),
            new \Twig_SimpleFunction('sessidd', array($this, 'sessidd')),
            new \Twig_SimpleFunction('userinfo', array($this, 'userinfo')),
            new \Twig_SimpleFunction('username', array($this, 'username')),
        );
    }

    public function priceFilter($number, $decimals = 0, $decPoint = '.', $thousandsSep = ',') {
        $price = number_format($number, $decimals, $decPoint, $thousandsSep);
        $price = '$' . $price;

        return $price;
    }

    public function sessidd() {
        return print_r($this->session, true);
    }

    public function logged() {
        $access_token = $this->session->get('access_token');
        if ($access_token == null) {
            $this->session->getFlashBag()->add('notice', 'logged: access_token is null');
            return false;
        }
        $d = IAMClient::isValid($access_token);
        $this->session->getFlashBag()->add('notice', 'logged: access_token is invalid: ' . print_r($d, true));
        if (property_exists($d, 'access_token') == false) {
            $this->session->getFlashBag()->add('notice', 'logged: access_token is invalid: ' . $access_token);
            return false;
        }
        if ($d->access_token == null) {
            $this->session->getFlashBag()->add('notice', 'logged: access_token is invalid: ' . $access_token);
            return false;
        }        
        $this->session->getFlashBag()->add('notice', 'logged: true');
        return true;
    }

    public function userinfo() {
        return print_r($this->session->get('user_info'), true);
    }

    public function username() {
        $i = $this->session->get('user_info');
        return $i->given_name . ' ' . $i->family_name;
    }

    public function getName() {
        return 'app_extension';
    }

}
