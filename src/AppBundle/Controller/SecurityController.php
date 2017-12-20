<?php

namespace AppBundle\Controller;

use AppBundle\Entity\JuniorDeveloper;
use AppBundle\Form\JuniorDeveloperType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@App/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error,
        ));
    }

    public function registrationAction(Request $request)
    {
        $juniorDeveloper = new JuniorDeveloper();
        $form = $this->createForm(new JuniorDeveloperType(), $juniorDeveloper);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($juniorDeveloper, $juniorDeveloper->getPassword());
            $juniorDeveloper->setPassword($password);
            $juniorDeveloper->setRoles('ROLE_USER');
            $em = $this->getDoctrine()->getManager();
            $em->persist($juniorDeveloper);
            $em->flush();
            return $this->redirectToRoute('main');
        }
        return $this->render(
            '@App/registration.html.twig',
            array('form' => $form->createView())
        );
    }
}
