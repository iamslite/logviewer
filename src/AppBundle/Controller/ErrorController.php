<?php
/**
 * @file
 * Controller for handling the Apache Error Log files
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model;

class ErrorController extends Controller
{
    /**
     * @Route("/error/", name="error_index")
     * @Route("/error/{logid}/{firstline}/{linecount}", defaults={"logid" = null, "firstline" = -50, "linecount" = 0}, name="error_log")
     */
    public function indexAction(Request $request, $logid = null, $firstline = 0, $linecount = 0)
    {
        $project = $this->get('app.project');

        # Protect against wildcards
        if (empty($logid) || preg_match('#^[[:alnum:]\./]*$#', $logid) != 1) {
            $logid = '*';
        }

        $log = $logs = null;

        if (!empty($logid)) 
        {
            $finder = $this->get('app.logFinder');

            $logs = $finder->find('Error', $logid);

            if (!empty($logs)) {
                if (count($logs) == 1) {
                    $log = reset($logs);

                    if ($log->getNumLines() < abs($firstline)) {
                        $firstline = 0;
                    }
                }
            }
        }

        return $this->render('error/index.html.twig', 
                             array(
                                 'log' => $log,
                                 'logs' => $logs,
                                 'project' => $project,
                                 'firstLine' => $firstline,
                                 'lineCount' => $linecount,
                                 'step' => (empty($linecount) ? 50 : $linecount),
                                 'route' => 'error_log',
                             )
        );
    }
}