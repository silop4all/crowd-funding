<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Project;
use AppBundle\Entity\ProjectOffer;
use AppBundle\Entity\User;
use AppBundle\Form\OfferType;
use AppBundle\Form\ProjectType;
use AppBundle\IAMClient;
use AppBundle\P4ARest;
use AppBundle\PayPal;
use DateInterval;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ProjectsController extends Controller {

    private $page_size = 10;

    /**
     * @Route("/projects", name="projects")
     */
    public function projectsAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $count = $repository->count();
        if ($count <= $this->page_size) {
            $body = "";
            $projects = $repository->findAllOrderedByName();
            foreach ($projects as $p) {
                $body .= $this->formatShort($p);
            }
            return $this->render('default/index2.html.twig', array(
                        'body' => $body,
            ));
        }
        $body = $this->getPaged(1, $this->page_size);

        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    private function getPaged($page, $page_size) {
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $from = ($page * $page_size) - $page_size;
        $proposals = $repository->findAllFromCount($from, $page_size);

        $body = "";
        foreach ($proposals as $p) {
            $body .= $this->formatShort($p);
        }
        $count = $repository->count();
        $body .= $this->pager($count, $page, $page_size);
        return $body;
    }

    private function pager($max, $current, $page_size) {
        $pages = $max / $page_size;
        $pager = '<ul class="pagination">';
        for ($i = 0; $i < $pages; $i++) {
            if (($current - 1) == $i) {
                $pager .= sprintf('<li class="active">');
            } else {
                $pager .= sprintf('<li>');
            }
            $url = $this->generateUrl('projectsPage', array('page' => ($i + 1)));
            $pager .= sprintf('<a href="%s">%d</a>', $url, $i + 1);
            $pager .= '</li>';
        }
        $pager .= '</ul>';
        return $pager;
    }

    /**
     * @Route("/projects/{page}", name="projectsPage")
     */
    public function projectsPageAction(Request $request, $page) {
        $body = $this->getPaged($page, $this->page_size);
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/project/view/{pid}", name="projectview")
     */
    public function projectViewAction(Request $request, $pid) {
        $project = $this->getDoctrine()->getRepository(Project::class)->find($pid);
        if (!$project) {
            throw $this->createNotFoundException('No Proposal found for id ' . $pid);
        }

        $body = $this->formatLong($project, $request);
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    private function formatLong($project) {
        $name = htmlspecialchars(($project->getTitle()));
        $summary = htmlspecialchars(($project->getSummary()));
        $content = ($project->getContent());
        $url = $this->generateUrl('pledge', array('pid' => $project->getId()));
        $body = "";
        $body .= sprintf('<div><div class="page-header">');
        $body .= sprintf('<h1>%s</h1></div>', $name);
        $body .= sprintf('<p class="lead">%s</p>', $summary);
        $body .= sprintf('<p class="misc">%s</p>', $content);
        $body .= sprintf('<a href="%s" class="btn btn-info" role="button">Back this project</a></div>', $url);
        return $body;
    }

    private function formatShort($project) {
        $name = htmlspecialchars(($project->getTitle()));
        $summary = htmlspecialchars(($project->getSummary()));
        $url = $this->generateUrl('projectview', array('pid' => $project->getId()));
        $body = "";
        $body .= sprintf('<div><div class="page-header">');
        $body .= sprintf('<h1>%s</h1></div>', $name);
        $body .= sprintf('<p class="lead">%s</p>', $summary);
        $body .= sprintf('<a href="%s">more...</a></div>', $url);
        return $body;
    }

    /**
     * @Route("/project/new", name="projectnew")
     */
    public function projectNewAction(Request $request) {
        $access_token = $request->getSession()->get('access_token');
        if ($access_token == null) {
            $url = $this->generateUrl('projectnew');
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }
        $d = IAMClient::isValid($access_token);
        if ($d->access_token == null) {
            $url = $this->generateUrl('projectnew');
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }

        $project = new Project();
        $project->setAmount(1000);
        $project->setPeriod(30);
        $now = new DateTime('now');
        $interval = new DateInterval('P30D');
        $now->add($interval);
        $project->setEtime($now);
        $project->setSummary('summary');
        $project->setTitle('title');
        $project->setContent('<b>content</b>');
        $form = $this->createForm(ProjectType::class, $project, array(
            'attr' => array(
                'class' => 'form-horizontal'
            )
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
//            P4ARest2::makeProject($em, $request->getSession(), $project, $this->getDoctrine());
            //P4ARest::makeProject($this->container->get('circle.restclient'), $request->getSession(), $project);

            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->find($request->getSession()->get('user_info')->sub);
            $project->setUser($user);

            $em->persist($project);
            $em->flush();
            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/project.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/pledgefinish/{pid}", name="pledgefinish")
     */
    public function pledgefinishAction(Request $request, $pid) {
        $paymentId = $request->query->get('paymentId');
        $token = $request->query->get('token');
        $PayerID = $request->query->get('PayerID');
        $pa = PayPal::authorize();
        $this->addFlash('notice', print_r($pa, true));
        $pa = PayPal::execute($pa->access_token, $paymentId, $PayerID);
        $this->addFlash('notice', print_r($pa, true));
        $p = P4ARest::execute($this->container->get('circle.restclient'), $request->getSession(), $paymentId, $PayerID);

        return $this->redirectToRoute('projectview', array('pid' => $pid));
    }

    /**
     * @Route("/pledge/{pid}", name="pledge")
     */
    public function pledgeAction(Request $request, $pid) {
        $access_token = $request->getSession()->get('access_token');
        if ($access_token == null) {
            $url = $this->generateUrl('pledge', array('pid' => $pid));
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }
        $d = IAMClient::isValid($access_token);
        if ($d->access_token == null) {
            $url = $this->generateUrl('offer', array('pid' => $pid));
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }

        //$p = P4ARest::getProjectObject($this->container->get('circle.restclient'), $pid);
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $p = $repository->find($pid);
        $offer = new Offer();
        $form = $this->createForm(OfferType::class, $offer, array(
            'attr' => array(
                'class' => 'form-horizontal'
            )
        ));
        $form
                ->add('submit', SubmitType::class, array('attr' => array(
                        'class' => 'btn btn-default'
                    ), 'label' => 'Submit'))
                ->add('cancel', SubmitType::class, array('attr' => array(
                        'class' => 'btn btn-default', 'formnovalidate' => 'formnovalidate'
                    ), 'label' => 'Cancel'));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getClickedButton() && 'cancel' === $form->getClickedButton()->getName()) {
                return $this->redirectToRoute('homepage');
            }

            //do paypal redirect

            $this->addFlash('notice', 'post offer');
            $pa = PayPal::authorize();
            $this->addFlash('notice', print_r($pa, true));
            $pobj = PayPal::create_payment(
                            $this->generateUrl('pledgefinish', array('pid' => $pid), true), $this->generateUrl('projectview', array('pid' => $pid), true), $offer->getAmount());
            $this->addFlash('notice', print_r($pobj, true));
            $pr = PayPal::payment($pa->access_token, $pobj, $d->access_token);
            $this->addFlash('notice', print_r($pr, true));

            //FOR IAM
            if (property_exists($pr, "payment"))
                $pr = $pr->payment;
            else {
                //error
                //return $this->redirectToRoute('homepage');
            }
            //else
            //  $pr = 0;
            //$offer
            $offer->setPaymentID($pr->id);
            //Logger::log("makePledge: " . print_r($offer, true));

            $poffer = new ProjectOffer();
            $poffer->setAmount($offer->getAmount());
            $poffer->setPaymentID($offer->getPaymentID());
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->find($request->getSession()->get('user_info')->sub);
            $poffer->setUser($user);
            $poffer->setProject($p);
            $em = $this->getDoctrine()->getManager();
            $em->persist($poffer);
            $em->flush();
            //$p = P4ARest::makePledge($this->container->get('circle.restclient'), $request->getSession(), $pid, $offer);
            //Logger::log("makePledge result: " . print_r($p, true));

            if (isset($pr->links))
                foreach ($pr->links as $l) {
                    if ($l->rel === 'approval_url') {
                        return $this->redirect($l->href);
                    }
                }
            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/offer.html.twig', array(
                    'project' => $p,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/project/delete/{pid}", name="projectdelete")
     */
    public function projectDeleteAction(Request $request, $pid) {

        $project = $this->getDoctrine()->getRepository(Project::class)->find($pid);
        if (!$project) {
            throw $this->createNotFoundException('No Project found for id ' . $pid);
        }
        $user = $this->getDoctrine()->getRepository(User::class)->find($request->getSession()->get('user_info')->sub);
        if ($project->getUser() !== $user) {
            throw $this->createNotFoundException('Invalid User');
        }
        $offers = $this->getDoctrine()->getRepository(ProjectOffer::class)->findBy(array('project' => $project));
        $em = $this->getDoctrine()->getManager();
        foreach ($offers as $offer) {
            $det = PayPal::showPaymentDetails($offer->getPaymentId());
            $pa = PayPal::authorize();
            if (count($det->transactions[0]->related_resources) > 0)
                PayPal::refund($pa->access_token, $det->transactions[0]->related_resources[0]->sale->id, null);
            $em->remove($offer);
        }
        $em->remove($project);
        $em->flush();
        $redirect = $this->generateUrl('myprojects');
        return new RedirectResponse($redirect);
    }

}
