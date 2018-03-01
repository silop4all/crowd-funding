<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AppBundle\Controller;

/**
 * Description of RegistrationController
 *
 * @author Panagiotis Minos
 */
// src/AppBundle/Controller/RegistrationController.php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectOffer;
use AppBundle\Entity\Proposal;
use AppBundle\Entity\ProposalOffer;
use AppBundle\Entity\User;
use AppBundle\IAMClient;
use AppBundle\Logger;
use AppBundle\P4ARest;
use AppBundle\PayPal;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends Controller {

    private $demo = true;

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request) {

        if ($this->demo) {
            $request->getSession()->set('access_token', 'demo');
            $genericObject = new \stdClass();
            $genericObject->sub = "sub";
            $genericObject->email = "email@gmail.com";
            $genericObject->name = "name";
            $genericObject->given_name = "given_name";
            $genericObject->family_name = "family_name";
            $request->getSession()->set('user_info', $genericObject);
            return $this->redirectToRoute('homepage');
        }
        $redirect = $this->generateUrl('amcb', array(), true);
        $url = IAMClient::generateLogin($redirect);
        return new RedirectResponse($url);
    }

    /**
     * @Route("/amcb", name="amcb")
     */
    public function logincbAction(Request $request) {
        $code = $request->get('code');
        $this->addFlash('notice', 'login callback: code: ' . $code);

        $redirect = $this->generateUrl('amcb', array(), true);
        $data = IAMClient::authorize($code, $redirect);
        $accessToken = "";
        if (false == property_exists($data, 'access_token')) {
            $this->addFlash('notice', 'login callback: authorize: ' . print_r($data, true));
            return $this->redirectToRoute('homepage');
        } else
            $accessToken = $data->access_token;
        $this->addFlash('notice', 'login callback: access_token: ' . $accessToken);
        $request->getSession()->set('access_token', $accessToken);
        $data2 = IAMClient::isValid($accessToken);
        $this->addFlash('notice', 'login callback: isValid: ' . $data2->access_token);
        $data3 = IAMClient::getUserInfo($accessToken);
        $request->getSession()->set('user_info', $data3);
        $this->addFlash('notice', 'login callback: user_info: ' . print_r($data3, true));

        $url = $request->getSession()->get('after.login.redirect');
        if ($url != null) {
            return $this->redirect($url);
        }
        return $this->redirectToRoute('homepage');
        return $this->render('default/index2.html.twig', array(
                    'body' => print_r($data, true) . $accessToken . print_r($data2, true) . print_r($data3, true),
        ));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(Request $request) {
        $referer = $this->getRequest()->headers->get('referer');
        $request->getSession()->set('access_token', '');
        return new RedirectResponse($referer);
    }

    /**
     * @Route("/signup", name="signup")
     */
    public function signupAction(Request $request) {
        //$referer = $this->getRequest()->headers->get('referer');
        $referer = IAMClient::generateSignUp();
        return new RedirectResponse($referer);
    }

    /**
     * @Route("/me", name="me")
     */
    public function meAction(Request $request) {
        $i = $request->getSession()->get('user_info');
        $body = '<div>';
        $body .= '<p>id: ' . $i->sub . '</p>';
        $body .= '<p>Username: ' . $i->name . '</p>';
        $body .= '<p>Given Name: ' . $i->given_name . '</p>';
        $body .= '<p>Family Name: ' . $i->family_name . '</p>';
        $body .= '<p>Email: ' . $i->email . '</p>';
        //$body .= print_r(IAMClient::getUserInfo2($request->getSession()->get('access_token')),true);
        //$body .= print_r(IAMClient::getUserInfo3($request->getSession()->get('access_token')),true);
        $roles = IAMClient::getRoles($request->getSession()->get('access_token'));
        if (!empty($roles)) {
            $body .= '<p>Roles: </p><ul>';
            foreach ($roles as $role) {
                $body .= '<li>' . $role . '</li>';
            }
            $body .= '</ul>';
        }
        $body .= '</div>';
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/my/projects", name="myprojects")
     */
    public function myProjectsAction(Request $request) {
//        $restClient = $this->container->get('circle.restclient');
//        $projects = P4ARest::getMyProjects($restClient, $request->getSession());
        $em = $this->getDoctrine()->getManager();
        $urepository = $this->getDoctrine()->getRepository(User::class);
        $prepository = $this->getDoctrine()->getRepository(Project::class);
        $orepository = $this->getDoctrine()->getRepository(ProjectOffer::class);
        $user = $urepository->find($request->getSession()->get('user_info')->sub);
        $projects = $prepository->findBy(array('user' => $user));

        $body = '<table class="table table-striped">';
        $body .= '<tr>';
        $body .= '<th>ID</th>';
        $body .= '<th>Project</th>';
        $body .= '<th>Offers</th>';
        $body .= '<th>Actions</th>';
        $body .= '</tr>';
        //if (is_object($projects))
        foreach ($projects as $e) {
            $url = $this->generateUrl('projectview', array('pid' => $e->getId()));
            $body .= '<tr>';
            $body .= '<td>' . $e->getId() . '</td>';
            $expired = '';
            if ($e->getExpired() === true)
                $expired = ' (expired)';
            $body .= '<td><a href="' . $url . '">' . $e->getTitle() . '</a>' . $expired . '</td>';


            $offers = $orepository->findBy(array('project' => $e));
            $amount = 0;
            foreach ($offers as $o)
                $amount += $o->getAmount();
            $body .= '<td>' . $amount . ' of ' . $e->getAmount() . '</td>';

            $durl = $this->generateUrl('projectdelete', array('pid' => $e->getId()));


            $body .= '<td><a href="' . $durl . '">' . 'Cancel' . '</a></td>';
            $body .= '</tr>';
        }
        $body .= '</table>';
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/my/pledges", name="mypledges")
     */
    public function myPledgesAction(Request $request) {
//        $restClient = $this->container->get('circle.restclient');
//        $pledges = P4ARest::getMyPledges($restClient, $request->getSession());
        $em = $this->getDoctrine()->getManager();
        $urepository = $this->getDoctrine()->getRepository(User::class);
        $prepository = $this->getDoctrine()->getRepository(Project::class);
        $orepository = $this->getDoctrine()->getRepository(ProjectOffer::class);
        $user = $urepository->find($request->getSession()->get('user_info')->sub);
        $pledges = $orepository->findBy(array('user' => $user));
        //$projects = $prepository->findBy(array('user' => $user));

        $body = '<table class="table table-striped">';
        $body .= '<tr>';
        $body .= '<th>ID</th>';
        $body .= '<th>Amount</th>';
        $body .= '<th>Project</th>';
        $body .= '<th>action</th>';
        $body .= '<th>PAYPAL</th>';
        $body .= '</tr>';
        //if (is_object($pledges))
        foreach ($pledges as $e) {
            $url = $this->generateUrl('projectview', array('pid' => $e->getProject()->getId()));
            $url2 = $this->generateUrl('deletemypledge', array('id' => $e->getId()));
            //$p = P4ARest::getProjectObject($restClient, $e->project);
            $p = $e->getProject();
            $body .= '<tr>';
            $body .= '<td>' . $e->getId() . '</td>';
            $body .= '<td>' . $e->getAmount() . '</td>';
            $body .= '<td><a href="' . $url . '">' . $p->getTitle() . '</a></td>';
            $body .= '<td><a href="' . $url2 . '">cancel</a></td>';
            //Logger::log(print_r($e, true));
            //Logger::log($e->getPaymentId());
            $det = '';
            $det = print_r(PayPal::showPaymentDetails($e->getPaymentId()), true);
            $body .= '<td>' . $det . '</td>';
            $body .= '</tr>';
        }
        $body .= '</table>';
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/my/pledges/delete/{id}", name="deletemypledge")
     */
    public function deleteMyPledgeAction(Request $request, $id) {
//        $restClient = $this->container->get('circle.restclient');
//        P4ARest::deleteMyPledge($restClient, $request->getSession(), $id);

        $offer = $this->getDoctrine()->getRepository(ProjectOffer::class)->find($id);
        if (!$offer) {
            throw $this->createNotFoundException('No Offer found for id ' . $pid);
        }
        $user = $this->getDoctrine()->getRepository(User::class)->find($request->getSession()->get('user_info')->sub);
        if ($offer->getUser() !== $user) {
            throw $this->createNotFoundException('Invalid User');
        }



        $det = PayPal::showPaymentDetails($offer->getPaymentId());
        $pa = PayPal::authorize();
        if (count($det->transactions[0]->related_resources) > 0)
            PayPal::refund($pa->access_token, $det->transactions[0]->related_resources[0]->sale->id, null);

        $em = $this->getDoctrine()->getManager();
        $em->remove($offer);

        $em->flush();


        $url = $this->generateUrl('mypledges');
        return new RedirectResponse($url);
    }

    /**
     * @Route("/my/proposals", name="myproposals")
     */
    public function myRequestsAction(Request $request) {
//        $restClient = $this->container->get('circle.restclient');
//        $proposals = P4ARest::getMyRequests($restClient, $request->getSession());
        $em = $this->getDoctrine()->getManager();
        $urepository = $this->getDoctrine()->getRepository(User::class);
        $prepository = $this->getDoctrine()->getRepository(Proposal::class);
        $orepository = $this->getDoctrine()->getRepository(ProposalOffer::class);
        $user = $urepository->find($request->getSession()->get('user_info')->sub);
        $proposals = $prepository->findBy(array('user' => $user));

        $body = '<table class="table table-striped">';
        $body .= '<tr>';
        $body .= '<th>ID</th>';
        $body .= '<th>Proposed Project</th>';
        $body .= '<th>Offers</th>';
        $body .= '<th>Actions</th>';
        $body .= '</tr>';
        //if (is_object($proposals))
        foreach ($proposals as $e) {
            $url = $this->generateUrl('proposalview', array('pid' => $e->getId()));
            $durl = $this->generateUrl('proposaldelete', array('pid' => $e->getId()));
            $body .= '<tr>';
            $body .= '<td>' . $e->getId() . '</td>';
            $body .= '<td><a href="' . $url . '">' . $e->getTitle() . '</a></td>';

            $offers = $orepository->findBy(array('proposal' => $e));
            $body .= '<td>' . count($offers) . '</td>';
            $body .= '<td><a href="' . $durl . '">' . 'Cancel' . '</a></td>';

            $body .= '</tr>';
        }
        $body .= "</table>";
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/my/offers", name="myoffers")
     */
    public function myOffersAction(Request $request) {
//        $restClient = $this->container->get('circle.restclient');
//        $offers = P4ARest::getMyOffers($restClient, $request->getSession());
        $em = $this->getDoctrine()->getManager();
        $urepository = $this->getDoctrine()->getRepository(User::class);
        $prepository = $this->getDoctrine()->getRepository(Proposal::class);
        $orepository = $this->getDoctrine()->getRepository(ProposalOffer::class);
        $user = $urepository->find($request->getSession()->get('user_info')->sub);
        $offers = $orepository->findBy(array('user' => $user));

        $body = '<table class="table table-striped">';
        $body .= '<tr>';
        $body .= '<th>ID</th>';
        $body .= '<th>Amount</th>';
        $body .= '<th>Proposed Project</th>';
        $body .= '<th>action</th>';
        $body .= '</tr>';
        //if (is_object($offers))
        foreach ($offers as $e) {
            $url = $this->generateUrl('proposalview', array('pid' => $e->getProposal()->getId()));
            $url2 = $this->generateUrl('deletemyoffer', array('id' => $e->getId()));

            //$p = P4ARest::getRequestObject($restClient, $e->projectRequest);
//                $p = $this->getDoctrine()->getRepository(\AppBundle\Entity\Proposal::class)->find($e->projectRequest);
//                if (!$p) {
//                    throw $this->createNotFoundException('No Proposal found for id ' . $e->get);
//                }

            $body .= '<tr>';
            $body .= '<td>' . $e->getId() . '</td>';
            $body .= '<td>' . $e->getAmount() . '</td>';
            $body .= '<td><a href="' . $url . '">' . $e->getProposal()->getTitle() . '</a></td>';
            $body .= '<td><a href="' . $url2 . '">cancel</a></td>';
            $body .= '</tr>';
        }
        $body .= '</table>';
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/my/offers/delete/{id}", name="deletemyoffer")
     */
    public function deleteMyOfferAction(Request $request, $id) {
//        $restClient = $this->container->get('circle.restclient');
//        P4ARest::deleteMyOffer($restClient, $request->getSession(), $id);

        $offer = $this->getDoctrine()->getRepository(ProposalOffer::class)->find($id);
        if (!$offer) {
            throw $this->createNotFoundException('No Offer found for id ' . $pid);
        }
        $user = $this->getDoctrine()->getRepository(User::class)->find($request->getSession()->get('user_info')->sub);
        if ($offer->getUser() !== $user) {
            throw $this->createNotFoundException('Invalid User');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($offer);

        $em->flush();

        $url = $this->generateUrl('myoffers');
        return new RedirectResponse($url);
    }

}
