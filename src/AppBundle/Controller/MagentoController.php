<?php
/**
 * @file
 * Controller for handling the Magento Logs
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model;

class MagentoController extends Controller
{
    /**
     * @Route("/magento/", name="magento_index")
     * @Route("/magento/{logid}/{firstline}/{linecount}", defaults={"logid" = null, "firstline" = -50, "linecount" = 0}, name="magento_log")
     */
    public function indexAction(Request $request, $logid = null, $firstline = 0, $linecount = 0)
    {
        $project = $this->get('app.project');

        $data = array();
        if (!empty($logid)) {
            $data['logid'] = $logid;
        }

        $form = $this->createFormBuilder($data)
              ->add('logid', 
                    'Symfony\Component\Form\Extension\Core\Type\TextType',
                    array('label' => 'Log', 'required' => false)
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

        $logs = $log = null;

        if (!empty($logid) || $form->isValid()) 
        {
            $finder = $this->get('app.logFinder');

            $logs = $finder->find('Magento Log', '*' . $logid . '*');

            if (!empty($logs)) {
                if (count($logs) == 1) {
                    $log = reset($logs);

                    if ($log->getNumLines() < abs($firstline)) {
                        $firstline = 0;
                    }
                }
            }
        }

        return $this->render('magento/index.html.twig', 
                             array(
                                 'form' => $form->createView(),
                                 'log' => $log,
                                 'logs' => $logs,
                                 'project' => $project,
                                 'firstLine' => $firstline,
                                 'lineCount' => $linecount,
                                 'step' => (empty($linecount) ? 50 : $linecount),
                                 'route' => 'magento_log',
                             )
        );
    }
}