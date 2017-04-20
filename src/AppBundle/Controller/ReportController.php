<?php
/**
 * @file
 * Controller for handling the Magento Reports
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model;

class ReportController extends Controller
{
    // * @Route("/report/{logid}/{firstline}/{linecount}", defaults={"logid" = null, "firstline" = -50, "linecount" = 0}, name="report_log")
    /**
     * @Route("/report/", name="report_index")
     * @Route("/report/{logid}", defaults={"logid" = null}, name="report_log")
     */
    public function indexAction(Request $request, $logid = null)
    {
        $project = $this->get('app.project');

        $data = array();
        if (!empty($logid)) {
            $data['logid'] = $logid;
        }

        $form = $this->createFormBuilder($data)
              ->add('logid', 
                    'Symfony\Component\Form\Extension\Core\Type\IntegerType',
                    array('label' => 'Report', 'required' => false)
              )
              ->add('show', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType', 
                    array('label' => 'Show')
              )
              ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $logid = $data['logid'];
        }

        $reports = $report = null;

        if (!empty($logid) || $form->isValid()) 
        {
            $finder = $this->get('app.logFinder');

            $reports = $finder->find('Magento Report', '*' . $logid . '*');

            if (!empty($reports)) {
                if (count($reports) == 1) {
                    $report = reset($reports);
                }
            }
        }

        return $this->render('report/index.html.twig', 
                             array(
                                 'form' => $form->createView(),
                                 'log' => $report,
                                 'logs' => $reports,
                                 'project' => $project,
                                 'route' => 'report_log',
                             )
        );
    }
}