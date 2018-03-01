<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle;

/**
 * Description of IAMClient
 *
 * @author Panagiotis Minos
 */
class IAMClient {

    private static $IAM_BASE = "http://83.235.169.221";
    static $CLIENT_ID = "";
    static $CLIENT_SECRET = "";
    static $demo = true;
    
    public static function generateLogin($redirect) {
        $url = self::$IAM_BASE . '/prosperity4all/identity-access-manager/oauth2/authorize?'
                . 'response_type=code'
                . '&client_id=' . self::$CLIENT_ID
                . '&redirect_uri=' . $redirect;
        return $url;
    }

    public static function generateSignUp() {
        return self::$IAM_BASE . '/prosperity4all/identity-access-manager/signup-request/';
    }

    public static function authorize($code, $redirect) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$IAM_BASE . '/openam/oauth2/access_token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/x-www-form-urlencoded",
            "Accept: application/json",
            "Authorization: Basic " . base64_encode(self::$CLIENT_ID . ":" . self::$CLIENT_SECRET)
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=authorization_code"
                . "&code=" . $code
                . "&redirect_uri=" . $redirect);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response);
        return $data;
    }

    public static function isValid($accessToken) {
        if(self::$demo) {
            
            $genericObject = new \stdClass();
            $genericObject->access_token = 'demo';
            return $genericObject;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$IAM_BASE . '/openam/oauth2/tokeninfo?access_token=' . $accessToken);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Accept: application/json"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data2 = json_decode($response);
        return $data2;
    }

    public static function getUserInfo($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$IAM_BASE . '/openam/oauth2/userinfo');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Bearer " . $accessToken
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data3 = json_decode($response);
        return $data3;
    }

    public static function getUserInfo2($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$IAM_BASE . '/prosperity4all/identity-access-manager/api/oauth2/userinfo?access_token=' . $accessToken);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/json"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data3 = json_decode($response);
        return $data3;
    }

    public static function getUserInfo3($accessToken) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$IAM_BASE . '/prosperity4all/identity-access-manager/api/oauth2/roles?access_token='
                . $accessToken . "&client_id=" . self::$CLIENT_ID);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/json"
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $data3 = json_decode($response);
        return $data3;
    }

    public static function getRoles($accessToken) {
        $info = IAMClient::getUserInfo3($accessToken);
        $roles = array();
        foreach ($info as $r) {
            if ($r != null && is_object($r) && property_exists($r, 'application_role') && $r->application_role != null && property_exists($r->application_role, 'role') && $r->application_role->role != null && property_exists($r->application_role->role, 'type') && $r->application_role->role->type != null) {
                $roles[] = $r->application_role->role->type;
            }
        }
        return $roles;
    }

}
