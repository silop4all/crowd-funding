<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle;

/**
 * Description of PayPal
 *
 * @author Panagiotis Minos
 */
class Logger {

    //https://developer.paypal.com/docs/integration/direct/make-your-first-call/
    private static $filename = '/tmp/p4all.log';

    public static function log($message) {
        $f = fopen(self::$filename, "a");
        fwrite($f, $message);
        fwrite($f, "\n");
        fclose($f);
    }

}
