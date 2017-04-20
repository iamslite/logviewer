<?php
/**
 * @file
 * Security/Form controller
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'security/login.html.twig',
            array(
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    /**
     * @Route("/login/password", name="password")
     */
    public function passwordAction(Request $request)
    {
        $data = array();

        $form = $this->createFormBuilder()
              ->add('password', 
                    'Symfony\Component\Form\Extension\Core\Type\PasswordType',
                    array('label' => 'Password', 'required' => false)
              )
              ->add('get', 
                    'Symfony\Component\Form\Extension\Core\Type\SubmitType', 
                    array('label' => 'Get')
              )
              ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $password = crypt($data['password'], '$2y$12$' . bin2hex(openssl_random_pseudo_bytes(22)));
        }
        else {
            $password = '';
        }

        return $this->render('security/password.html.twig',
                             array(
                                 'form' => $form->createView(),
                                 'password' => $password,
                             )
        );
    }
}