<?php
/**
 * @file
 * Controller for handling the PHP Log files
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use AppBundle\Model;

class PhpController extends Controller
{
    /**
     * @Route("/php/{firstline}/{linecount}", defaults={"firstline" = -5, "linecount" = 0}, name="php_index")
     */
    public function indexAction(Request $request, $firstline = 0, $linecount = 0)
    {
        $project = $this->get('app.project');

        $logid = 'php.errors';

        $log = null;

        if (!empty($logid)) 
        {
            $finder = $this->get('app.logFinder');

            $logs = $finder->find('PHP', '*' . $logid . '*');

            if (!empty($logs)) {
                if (count($logs) == 1) {
                    $log = reset($logs);

                    if ($log->getNumLines() < abs($firstline)) {
                        $firstline = 0;
                    }
                }
            }
        }

        return $this->render('log/index.html.twig', 
                             array(
                                 'log' => $log,
                                 'project' => $project,
                                 'firstLine' => $firstline,
                                 'lineCount' => $linecount,
                                 'step' => (empty($linecount) ? 5 : $linecount),
                                 'route' => 'php_index',
                                 'route_defaults' => array(),
                             )
        );
    }
}