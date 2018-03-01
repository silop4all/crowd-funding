<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class PWController extends Controller {

    /**
     * @Route("/paypal/webhook", name="paypalwebhook")
     */
    public function homeAction(Request $request) {

	$file = '/tmp/web.txt';
	$params = $request->request->all();
        $p = print_r($params,true);
	file_put_contents($file, "---\n", FILE_APPEND | LOCK_EX);
	file_put_contents($file, $request->getContent(), FILE_APPEND | LOCK_EX);
        return new Response('ok', Response::HTTP_OK);;
    }


}
