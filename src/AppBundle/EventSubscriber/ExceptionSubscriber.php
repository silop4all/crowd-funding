<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ExceptionSubscriber
 *
 * @author 
 */

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\ProjectOffer;
use AppBundle\PayPal;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface {

    private $em;

    /**
     * @param EntityManager
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public static function getSubscribedEvents() {
        // return the subscribed events, their methods and priorities
        return array(
            KernelEvents::REQUEST => 'processException'
        );
    }

    public function processException(GetResponseEvent $event) {
        //$offers = $this->getDoctrine()->getRepository(Project::class)->findBy(array('ctime' => $project));
        //$event->getRequest()->

        $r = $this->em
                ->createQuery('SELECT e FROM AppBundle:Project e WHERE e.etime < CURRENT_TIMESTAMP()')
                ->getResult();

        foreach ($r as $project) {
            //$project = $this->getDoctrine()->getRepository(Project::class)->find($p->getId());
            $offers = $this->em->getRepository(ProjectOffer::class)->findBy(array('project' => $project));
            foreach ($offers as $offer) {
                $det = PayPal::showPaymentDetails($offer->getPaymentId());
                $pa = PayPal::authorize();
                if (count($det->transactions[0]->related_resources) > 0)
                    PayPal::refund($pa->access_token, $det->transactions[0]->related_resources[0]->sale->id, null);
                $this->em->remove($offer);
            }
            $project->setExpired(true);
            $this->em->persist($project);
            $this->em->flush();
        }
    }

}
