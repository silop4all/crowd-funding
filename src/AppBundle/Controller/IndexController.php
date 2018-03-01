<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\P4ARest;
use AppBundle\Form\ProjectType;
use AppBundle\Entity\Project;
use AppBundle\Entity\Category;

class IndexController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function homeAction(Request $request) {
        //$projects = P4ARest::getProjects($this->container->get('circle.restclient'));
        $repository = $this->getDoctrine()->getRepository(Project::class);
        $projects = $repository->findAll();
        $body = "";
        $body = '<div class="jumbotron">
  <h1>Welcome to crow-funding platform</h1>
  <p>Welcome to crow-funding platform</p>
  <p><a class="btn btn-primary btn-lg" href="/about" role="button">Learn more</a></p>
</div>';
        if ($projects == null) {
            
        } else {
            $body .= '<div class="container"><div class="row">';
            $i = 0;
            foreach ($projects as $p) {
                $i++;
                if ($i == 4) {
                    $i = 1;
                    $body .= '</div><div class="row">';
                }
                $name = htmlspecialchars(($p->getTitle()));
                $summary = htmlspecialchars(($p->getSummary()));
                $url = $this->generateUrl('projectview', array('pid' => $p->getId()));
                $body .= sprintf('<div class="col-md-4">');
                $body .= sprintf('<h2>%s</h2>', $name);
                $body .= sprintf('<p>%s</p>', $summary);
                $body .= sprintf('<p><a role="button" class="btn btn-default" href="%s">more &raquo;</a></p></div>', $url);
            }
            $body .= '</div></div>';
        }
        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

//FIXME need description text
    /**
     * @Route("/about", name="about")
     */
    public function aboutEditAction(Request $request) {

        return $this->render('default/index2.html.twig', array(
                    'body' => "<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante. Donec eu libero sit amet quam egestas semper. Aenean ultricies mi vitae est. Mauris placerat eleifend leo.</p>",
        ));
    }

    /**
     * @ Route("/project/edit/{pid}", name="projectedit")
     */
    public function projectEditAction(Request $request, $pid) {

        return $this->render('default/index2.html.twig', array(
                    'base_dir' => "",
        ));
    }

    /**
     * @ Route("/proposal/edit/{pid}", name="proposaledit")
     */
    public function proposalEditAction(Request $request, $pid) {

        return $this->render('default/index2.html.twig', array(
                    'base_dir' => "",
        ));
    }

    /**
     * @ Route("/categories", name="categories")
     */
    public function categoriesAction(Request $request) {
        $body = "";
        $categories = P4ARest::getCategories(null);


        foreach ($categories as $c) {
            $body .= $c->getTitle();
        }

        return $this->render('default/index2.html.twig', array(
                    'body' => $body,
        ));
    }

    /**
     * @ Route("/css/{file}", name="css")
     */
    public function cssAction($file) {
        $file = "web/css/" . $file;

        if (file_exists($file)) {
            $response = new Response(file_get_contents($file));
            $response->headers->set('Content-Type', 'text/css');
            return $response;
        } else {
            throw NotFoundHttpException($file . "  Not Found.");
        }
    }

    /**
     * @ Route("/bundles/{file}", name="bundles",requirements={"file"=".+"})
     */
    public function bundlesAction($file) {
        $file = "web/bundles/" . $file;

        if (file_exists($file)) {
            $response = new Response(file_get_contents($file));
            //$response->headers->set('Content-Type', 'text/css');
            return $response;
        } else {
            throw NotFoundHttpException($file . "  Not Found.");
        }
    }

    /**
     * @ Route("/uploads/{file}", name="uploads",requirements={"file"=".+"})
     */
    public function uploadsAction($file) {
        $file = "web/uploads/" . $file;

        if (file_exists($file)) {
            $response = new Response(file_get_contents($file));
            //$response->headers->set('Content-Type', 'text/css');
            return $response;
        } else {
            throw NotFoundHttpException($file . "  Not Found.");
        }
    }

}
