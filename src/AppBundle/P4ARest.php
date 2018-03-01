<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle;

use AppBundle\Logger;
use Rest\Project;
use AppBundle\Entity\Category;

/**
 * Description of P4ARest
 *
 * @author Panagiotis Minos
 */
class P4ARest {

    private static $base = //'http://localhost:8084/p4all-rest/rest';
            'http://rest.p4all.ioperm.org/p4all-rest/rest';

    public static function getProjects($restClient) {
        $r = $restClient->get(self::$base . '/secure/project?key=0&limit=10&order=desc');
        $j = json_decode($r->getContent());
        return $j;
    }

    public static function getProjectsLimit($restClient, $from, $to) {
        $r = $restClient->get(self::$base . '/secure/project/'
                . $from
                . '/'
                . $to
                . '?key=0');
        $j = json_decode($r->getContent());
        return $j;
    }

    public static function getProposals($restClient) {
        $r = $restClient->get(self::$base . '/secure/projectrequest?key=0&limit=10&order=desc');
        $j = json_decode($r->getContent());
        return $j;
    }

    public static function getProposalsLimit($restClient, $from, $to) {
        $r = $restClient->get(self::$base . '/secure/projectrequest/'
                . $from
                . '/'
                . $to
                . '?key=0');
        $j = json_decode($r->getContent());
        return $j;
    }

//    public static function getProject($restClient, $pid) {
//        $r = $restClient->get(self::$base.'/secure/project/' . $pid . '?key=0');
//        $p = json_decode($r->getContent());
//        $body = "";
//        //$body .= print_r($p,true);
//        $body .= sprintf("<div class=\"page-header\"><h1>%s</h1></div><p class=\"lead\">%s</p><p class=\"misc\">%s</p>"
//                . '<a href="/pledge/%d" class="btn btn-info" role="button">Back this project</a>', htmlspecialchars(P4ARest::fix($p->name)), htmlspecialchars(P4ARest::fix($p->description)),
//                //htmlspecialchars
//                (P4ARest::fix($p->content)), $p->id
//        );
//
//        return $body;
//    }

    public static function getProjectObject($restClient, $pid) {
        $r = $restClient->get(self::$base . '/secure/project/' . $pid . '?key=0');
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function deleteMyOffer($restClient, $session, $id) {
        $access_token = $session->get('access_token');
        $r = $restClient->delete(self::$base . '/offer/' . $id . '?key=0&access_token=' . $access_token);
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function deleteMyPledge($restClient, $session, $id) {
        $access_token = $session->get('access_token');
        $r = $restClient->delete(self::$base . '/bid/' . $id . '?key=0&access_token=' . $access_token);
        $p = json_decode($r->getContent());
        return $p;
    }

//    public static function getProjectPledge($restClient, $pid) {
//        $r = $restClient->get(self::$base.'/secure/project/' . $pid . '?key=0');
//        $p = json_decode($r->getContent());
//        $body = "";
//        //$body .= print_r($p,true);
//        $body .= sprintf("<div class=\"page-header\"><h1>%s</h1></div><p class=\"lead\">%s</p><p class=\"misc\">%s</p>"
//                . '<a href="/back/%d" class="btn btn-info" role="button">Back this project</a>', htmlspecialchars(P4ARest::fix($p->name)), htmlspecialchars(P4ARest::fix($p->description)),
//                //htmlspecialchars
//                (P4ARest::fix($p->content)), $p->id
//        );
//
//        return $body;
//    }
//    public static function getRequests($restClient) {
//        $r = $restClient->get(self::$base.'/secure/projectrequest?key=0&limit=10&order=desc');
//        $j = json_decode($r->getContent());
//        $body = "";
//        foreach ($j->requests as $pid => $p) {
//            //$body .= print_r($p,true);
//            $body .= sprintf("<div class=\"page-header\"><h1>%s</h1></div><p class=\"lead\">%s</p><a href=\"/proposal/view/%d\">more...</a>", htmlspecialchars(P4ARest::fix($p->name)), htmlspecialchars(P4ARest::fix($p->description)), $p->id
//            );
//        }
//        return $body;
//    }
//    public static function getRequest($restClient, $pid) {
//        $r = $restClient->get(self::$base.'/projectrequest/' . $pid . '?key=0');
//        $p = json_decode($r->getContent());
//        $body = "";
//        //$body .= print_r($p,true);
//        $body .= sprintf("<div class=\"page-header\"><h1>%s</h1></div><p class=\"lead\">%s</p><p class=\"misc\">%s</p>"
//                . '<a href="/offer/%d" class="btn btn-info" role="button">Make an Offer</a>', htmlspecialchars(P4ARest::fix($p->name)), htmlspecialchars(P4ARest::fix($p->description)),
//                //htmlspecialchars
//                (P4ARest::fix($p->content)), $p->id
//        );
//
//        return $body;
//    }

    public static function getRequestObject($restClient, $pid) {
        $r = $restClient->get(self::$base . '/secure/projectrequest/' . $pid . '?key=0');
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function makeOffer($restClient, $session, $pid, $offer) {
        $access_token = $session->get('access_token');
        $payload = new \AppBundle\Rest\Offer();
        $payload->user = $session->get('user_info')->sub;
        $payload->projectRequest = $pid;
        $payload->amount = $offer->getAmount();
        $session->getFlashBag()->add('notice', json_encode($payload));
        $r = $restClient->post(self::$base . '/offer?key=0&access_token=' . $access_token, json_encode($payload), array(CURLOPT_HTTPHEADER => array('Content-Type: application/json')));
        $session->getFlashBag()->add('notice', $r->getContent());
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function makePledge($restClient, $session, $pid, $offer) {
        $access_token = $session->get('access_token');
        $payload = new \AppBundle\Rest\Pledge();
        $payload->user = $session->get('user_info')->sub;
        $payload->project = $pid;
        $payload->amount = $offer->getAmount();
        $payload->paymentID = ($offer->getPaymentID());
        $session->getFlashBag()->add('notice', json_encode($payload));
        Logger::log("makePledge: " . print_r($payload, TRUE));
        Logger::log("makePledge: " . self::$base . '/bid?key=0&access_token=' . $access_token);
        $r = $restClient->post(self::$base . '/bid?key=0&access_token=' . $access_token, json_encode($payload), array(CURLOPT_HTTPHEADER => array('Content-Type: application/json')));
        Logger::log("makePledge post: " . print_r($r, TRUE));

        $session->getFlashBag()->add('notice', $r->getContent());
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function execute($restClient, $session, $payment, $payer) {
        $access_token = $session->get('access_token');
        //$payload = new \AppBundle\Rest\Pledge();
        //$payload->user = $session->get('user_info')->sub;
        //$payload->project = $pid;
        //$payload->amount = $offer->getAmount();
        //$payload->paymentID = ($offer->getPaymentID());
        //$session->getFlashBag()->add('notice', json_encode($payload));
        //Logger::log("makePledge: " . print_r($payload, TRUE));
        //Logger::log("makePledge: " . self::$base . '/bid?key=0&access_token=' . $access_token);
        $payload = new \stdClass();
        $payload->payer = $payer;
        $payload->payment = $payment;
        $r = $restClient->post(self::$base . '/bid/execute?key=0&access_token=' . $access_token,
                json_encode($payload), array(CURLOPT_HTTPHEADER => array('Content-Type: application/json')));
        //Logger::log("makePledge post: " . print_r($r, TRUE));

        //$session->getFlashBag()->add('notice', $r->getContent());
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function makeProject($restClient, $session, $entity) {
        $access_token = $session->get('access_token');
        $payload = new \AppBundle\Rest\Project();
        $payload->user = $session->get('user_info')->sub;
        $payload->name = $entity->getTitle();
        $payload->description = $entity->getSummary();
        $payload->content = $entity->getContent();
        $payload->amount = $entity->getAmount();
        $payload->period = $entity->getPeriod();
        $session->getFlashBag()->add('notice', json_encode($payload));
        $r = $restClient->post(self::$base . '/project?key=0&access_token=' . $access_token, json_encode($payload), array(CURLOPT_HTTPHEADER => array('Content-Type: application/json')));
        $session->getFlashBag()->add('notice', $r->getContent());
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function makeProposal($restClient, $session, $entity) {
        $access_token = $session->get('access_token');
        $payload = new \AppBundle\Rest\Proposal();
        $payload->name = $entity->getTitle();
        $payload->description = $entity->getSummary();
        $payload->content = $entity->getContent();
        $session->getFlashBag()->add('notice', json_encode($payload));
        $r = $restClient->post(self::$base . '/request?key=0&access_token=' . $access_token, json_encode($payload), array(CURLOPT_HTTPHEADER => array('Content-Type: application/json')));
        $session->getFlashBag()->add('notice', $r->getContent());
        $p = json_decode($r->getContent());
        return $p;
    }

    public static function getMyOffers($restClient, $session) {
        $r = $restClient->get(self::$base . '/offer?key=0&access_token=' . $session->get('access_token'));
        $j = json_decode($r->getContent());
        return $j;
    }

    public static function getMyPledges($restClient, $session) {
        $r = $restClient->get(self::$base . '/bid?key=0&access_token=' . $session->get('access_token'));
        $j = json_decode($r->getContent());
        return $j;
    }

    public static function getMyProjects($restClient, $session) {
        $r = $restClient->get(self::$base . '/project?key=0&access_token=' . $session->get('access_token'));
        $j = json_decode($r->getContent());
        return $j;
    }

    public static function getMyRequests($restClient, $session) {
        $r = $restClient->get(self::$base . '/request?key=0&access_token=' . $session->get('access_token'));
        $j = json_decode($r->getContent());
        return $j;
    }

    public static function getCategories($restClient) {
        $a = array();
        $c = new Category();
        $c->setTitle("Art");
        $a[] = $c;
        $c = new Category();
        $c->setTitle("Publishing");
        $a[] = $c;

        $c = new Category();
        $c->setTitle("Technology");
        $a[] = $c;

        return $a;
    }

    public static function fix($v) {
        if (empty($v)) {
            return 'no content';
        }
        return $v;
    }

}
