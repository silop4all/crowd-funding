<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Project;
use AppBundle\Entity\Proposal;
use AppBundle\Entity\ProposalOffer;
use AppBundle\Entity\User;
use AppBundle\Form\ProposalOfferType;
use AppBundle\Form\ProposalType;
use AppBundle\IAMClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class ProposalsController extends Controller {

    private $page_size = 10;

    /**
     * @Route("/proposals", name="proposals")
     */
    public function proposalsAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository(Proposal::class);
        $count = $repository->count();
        if ($count <= $this->page_size) {
            $body = "";
            $proposals = $repository->findAllOrderedByName();
            foreach ($proposals as $p) {
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
        $repository = $this->getDoctrine()->getRepository(Proposal::class);
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
            $url = $this->generateUrl('proposalsPage', array('page' => ($i + 1)));
            $pager .= sprintf('<a href="%s">%d</a>', $url, $i + 1);
            $pager .= '</li>';
        }
        $pager .= '</ul>';
        return $pager;
    }

    /**
     * @Route("/proposals/{page}", name="proposalsPage")
     */
    public function proposalsPageAction(Request $request, $page) {
        $body = $this->getPaged($page, $this->page_size);
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/proposal/view/{pid}", name="proposalview")
     */
    public function proposalViewAction(Request $request, $pid) {

        $project = $this->getDoctrine()->getRepository(Proposal::class)->find($pid);
        if (!$project) {
            throw $this->createNotFoundException('No Proposal found for id ' . $pid);
        }

        $body = $this->formatLong($project, $request);
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @Route("/proposal/delete/{pid}", name="proposaldelete")
     */
    public function proposalDeleteAction(Request $request, $pid) {

        $project = $this->getDoctrine()->getRepository(Proposal::class)->find($pid);
        if (!$project) {
            throw $this->createNotFoundException('No Proposal found for id ' . $pid);
        }
        $user = $this->getDoctrine()->getRepository(User::class)->find($request->getSession()->get('user_info')->sub);
        if ($project->getUser() !== $user) {
            throw $this->createNotFoundException('Invalid User');
        }
        $offers = $this->getDoctrine()->getRepository(ProposalOffer::class)->findBy(array('proposal' => $project));
        $em = $this->getDoctrine()->getManager();
        foreach ($offers as $e) {
            $em->remove($e);
        }
        $em->remove($project);
        $em->flush();
        $redirect = $this->generateUrl('myproposals');
        return new RedirectResponse($redirect);
    }

    private function formatLong(Proposal $project, Request $request) {

        $user = $this->getDoctrine()->getRepository(User::class)->find($request->getSession()->get('user_info')->sub);



        $name = htmlspecialchars($project->getTitle());
        $summary = htmlspecialchars($project->getSummary());
        $content = $project->getContent();
        $url = $this->generateUrl('offer', array('pid' => $project->getId()));
        $body = "";
        $body .= sprintf('<div><div class="page-header">');
        $body .= sprintf('<h1>%s</h1></div>', $name);
        $body .= sprintf('<p class="lead">%s</p>', $summary);
        $body .= sprintf('<p class="misc">%s</p>', $content);
        $body .= sprintf('<a href="%s" class="btn btn-info" role="button">Make an offer</a></div>', $url);

        if ($user === $project->getUser()) {

            $offers = $this->getDoctrine()->getRepository(ProposalOffer::class)->findBy(array('proposal' => $project));
            if (count($offers) > 0) {
                $body .= "<h2>Offers</h2>";

                $body .= "<div><table class=\"table table-striped\">";
                $body .= "<tr><th>Surname</th><th>Name</th><th>Amount</th><th>Action</th>";
                $body .= "</tr>";
                foreach ($offers as $offer) {
                    $body .= "<tr>";
                    $url = $this->generateUrl('acceptoffer', array('id' => $offer->getId()));
                    $body .= sprintf('<td>%s</td><td>%s</td><td>%s</td>', $offer->getUser()->getSurname(), $offer->getUser()->getName(), $offer->getAmount());
                    $body .= sprintf('<td><a href="%s" class="btn btn-info" role="button">Accept offer</a>', $url);
                    $body .= sprintf('</td>');
                    $body .= "</tr>";
                }
                $body .= "</table></div>";
            }
        }

        return $body;
    }

    /**
     * @Route("/proposal/accept/{id}", name="acceptoffer")
     */
    public function proposalAcceptAction(Request $request, $id) {
        $user = $this->getDoctrine()->getRepository(User::class)->find($request->getSession()->get('user_info')->sub);


        $offer = $this->getDoctrine()->getRepository(ProposalOffer::class)->find($id);
        if (!$offer) {
            throw $this->createNotFoundException('No Proposal found for id ' . $pid);
        }

        $offers = $this->getDoctrine()->getRepository(ProposalOffer::class)->findBy(array('proposal' => $offer->getProposal()));
        $em = $this->getDoctrine()->getManager();
        foreach ($offers as $e) {
            if ($e !== $offer)
                $em->remove($e);
        }

        $p = new Project();
        $p->setAmount($offer->getAmount());
        $p->setTitle($offer->getProposal()->getTitle());
        $p->setSummary($offer->getProposal()->getSummary());
        $p->setContent($offer->getProposal()->getContent());
        $p->setUser($offer->getUser())     ;
        $em->persist($p);
        $em->flush();
        $redirect = $this->generateUrl('myproposals');
        return new RedirectResponse($redirect);
    }

    private function formatShort($project) {
        $name = htmlspecialchars($project->getTitle());
        $summary = htmlspecialchars($project->getSummary());
        $url = $this->generateUrl('proposalview', array('pid' => $project->getId()));
        $body = "";
        $body .= sprintf('<div><div class="page-header">');
        $body .= sprintf('<h1>%s</h1></div>', $name);
        $body .= sprintf('<p class="lead">%s</p>', $summary);
        $body .= sprintf('<a href="%s">more...</a></div>', $url);
        return $body;
    }

    /**
     * @Route("/proposal/new", name="proposalnew")
     */
    public function proposalNewAction(Request $request) {
        $access_token = $request->getSession()->get('access_token');
        if ($access_token == null) {
            $url = $this->generateUrl('proposalnew');
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }
        $d = IAMClient::isValid($access_token);
        if ($d->access_token == null) {
            $url = $this->generateUrl('proposalnew');
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }

        $project = new Proposal();
        $project->setAmount(1000);
        $project->setSummary('proposal summary');
        $project->setTitle('proposal title');
        $project->setContent('<b>proposal html content</b>');
        $form = $this->createForm(ProposalType::class, $project, array(
            'attr' => array(
                'class' => 'form-horizontal'
            )
        ));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //P4ARest::makeProposal($this->container->get('circle.restclient'), $request->getSession(), $project);
            $em = $this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(User::class);
            $user = $repository->find($request->getSession()->get('user_info')->sub);
            $project->setUser($user);

            $em->persist($project);
            $em->flush();
            $this->sendNewProposalMail($project);

            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/proposal.html.twig', array('form' => $form->createView()));
    }

    private function sendNewProposalMail($project) {
        $message = (new \Swift_Message($project->getTitle()))
                ->setFrom('p4all.system@gmail.com')
                ->setTo('p4all.system@gmail.com')
                ->setBody(
                $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'Emails/registration.html.twig', array('name' => $project->getTitle())
                ), 'text/html'
                )
        /*
         * If you also want to include a plaintext version of the message
          ->addPart(
          $this->renderView(
          'Emails/registration.txt.twig',
          array('name' => $name)
          ),
          'text/plain'
          )
         */
        ;

        $this->get('mailer')->send($message);

        //return $this->render(...);
    }

    /**
     * @Route("/offer/{pid}", name="offer")
     */
    public function offerAction(Request $request, $pid) {
        $access_token = $request->getSession()->get('access_token');
        if ($access_token == null) {
            $url = $this->generateUrl('offer', array('pid' => $pid));
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }
        $d = IAMClient::isValid($access_token);
        if ($d->access_token == null) {
            $url = $this->generateUrl('offer', array('pid' => $pid));
            $request->getSession()->set('after.login.redirect', $url);
            return $this->redirectToRoute('login');
        }

        $em = $this->getDoctrine()->getManager();
        $p = $this->getDoctrine()->getRepository(Proposal::class)
                ->find($pid);

        if (!$p) {
            throw $this->createNotFoundException(
                    'No Proposal found for id ' . $pid
            );
        }

        //$p = P4ARest::getRequestObject($this->container->get('circle.restclient'), $pid);

        $offer = new ProposalOffer();
        $form = $this->createForm(ProposalOfferType::class, $offer, array(
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
            //$this->addFlash('notice', 'post offer');
            //$p = P4ARest::makeOffer($this->container->get('circle.restclient'), $request->getSession(), $pid, $offer);


            $em = $this->getDoctrine()->getManager();
            $urepository = $this->getDoctrine()->getRepository(User::class);
            $prepository = $this->getDoctrine()->getRepository(Proposal::class);
            $orepository = $this->getDoctrine()->getRepository(ProposalOffer::class);
            $user = $urepository->find($request->getSession()->get('user_info')->sub);

            $offer->setUser($user);
            $offer->setProposal($p);

            $em->persist($offer);
            $em->flush();


            return $this->redirectToRoute('homepage');
        }

        return $this->render('default/offer.html.twig', array(
                    'project' => $p,
                    'form' => $form->createView(),
        ));
    }

}
