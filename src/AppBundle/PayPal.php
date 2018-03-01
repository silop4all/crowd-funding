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
class PayPal {

    //https://developer.paypal.com/docs/integration/direct/make-your-first-call/
    private static $ClientID = '';
    private static $Secret = '';
    private static $PAYPAL_URL = 'https://api.sandbox.paypal.com';
    private static $PAYMENT_URL = 'https://api.sandbox.paypal.com';

    //private static $PAYMENT_URL = 'http://localhost:8000/api';

    public static function authorize() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, self::$PAYPAL_URL . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/json",
            "Accept-Language: en_US",
                //"Authorization: Basic " . base64_encode(self::$CLIENT_ID . ":" . self::$CLIENT_SECRET)
        ));
        curl_setopt($ch, CURLOPT_USERPWD, self::$ClientID . ":" . self::$Secret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $response = curl_exec($ch);
        curl_close($ch);
        //print_r($response);
        $data = json_decode($response);
        return $data;
    }

    public static function payment($token, $payment, $access_token) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, self::$PAYMENT_URL . '/v1/payments/payment');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            //"Accept-Language: en_US",
            "Authorization: Bearer " . $token,
            "Openam-Client: " . IAMClient::$CLIENT_ID,
//            "Openam-Client-Token: " . IAMClient::$CLIENT_SECRET,
            "Openam-Client-Token: " . $access_token,
            "Paypal-Access-Token: " . $token,
        ));
        //curl_setopt($ch, CURLOPT_USERPWD, self::$ClientID . ":" . self::$Secret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        return $data;
    }

    public static function create_payment($redirect, $cancel, $amount) {
        $obj = new \stdClass();
        $obj->intent = "sale";
        $obj->redirect_urls = new \stdClass();

        if ($redirect) {
            $obj->redirect_urls->return_url = $redirect;
        } else {
            $obj->redirect_urls->return_url = 'http://localhost.ioperm.org:8000';
        }
        if ($cancel) {
            $obj->redirect_urls->cancel_url = $cancel;
        } else {
            $obj->redirect_urls->cancel_url = 'http://localhost.ioperm.org:8000';
        }
        $obj->payer = new \stdClass();
        $obj->payer->payment_method = 'paypal';
        $obj->transactions = array();
        $obj->transactions[] = new \stdClass();

        $obj->transactions[0]->amount = new \stdClass();
        $obj->transactions[0]->amount->total = '' . $amount;
        $obj->transactions[0]->amount->currency = 'USD';

        $obj->note_to_payer = 'prosperity4all';
        return $obj;
    }

    //https://developer.paypal.com/docs/api/payments/#payment_get
    public static function showPaymentDetails($id) {
        $pa = PayPal::authorize();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, self::$PAYMENT_URL . '/v1/payments/payment/' . $id . '');
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            //"Accept-Language: en_US",
            "Authorization: Bearer " . $pa->access_token
        ));
        //curl_setopt($ch, CURLOPT_USERPWD, self::$ClientID . ":" . self::$Secret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        error_log($response, 0);
        return $data;
    }

    public static function execute($token, $id, $payer) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, self::$PAYMENT_URL . '/v1/payments/payment/' . $id . '/execute');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            //"Accept-Language: en_US",
            "Authorization: Bearer " . $token
        ));
        //curl_setopt($ch, CURLOPT_USERPWD, self::$ClientID . ":" . self::$Secret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $obj = new \stdClass();
        $obj->payer_id = $payer;
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($obj));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        error_log($response, 0);

        return $data;
    }

    public static function refund($token, $id, $payer=null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, self::$PAYMENT_URL . '/v1/payments/sale/' . $id . '/refund');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            //"Accept-Language: en_US",
            "Authorization: Bearer " . $token
        ));
        //curl_setopt($ch, CURLOPT_USERPWD, self::$ClientID . ":" . self::$Secret);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $obj = new \stdClass();
        //$obj->payer_id = $payer;
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($obj));
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        error_log($response, 0);

        return $data;
    }

}
