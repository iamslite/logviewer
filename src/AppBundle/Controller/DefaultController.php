<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model\Project;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        //new Project();
        $project = $this->get('app.project');

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', array(
            'project' => $project,
        ));
    }
}
