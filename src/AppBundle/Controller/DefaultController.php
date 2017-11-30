<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BookDocument;
use AppBundle\Entity\JuniorDeveloper;
use AppBundle\Form\BookDocumentType;
use AppBundle\Form\JuniorDeveloperType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        $queryString = $request->getQueryString();

        $currentUser = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(BookDocument::class);
        $bookDocuments = $repository->findAll();

        $newDocument = new BookDocument();
        $form = $this->createFormBuilder($newDocument)
            ->add('name', 'text')
            ->add('url', 'text')
            ->add('save', 'submit', array('label' => 'Új felvétele'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newDocument = $form->getData();
            $newDocument->setAuthor($currentUser->getDisplayName());
            $em->persist($newDocument);
            $em->flush();

            return $this->redirectToRoute('main');
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->redirectToRoute('main',
                array('urlInvalid' => true)
            );
        }

        $reviewFixIssues = $this->getJiraCalculator()->getReviewFixes($currentUser->getUsername());
        $reviewFixParents = $reviewFixIssues['parents'];
        $reviewFixTimes = $reviewFixIssues['reviewFixTimes'];
        $appIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-86', $currentUser->getUsername());
        $bookIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-87', $currentUser->getUsername());
        $board = $this->get('trello_api')->getBoard();
        return $this->render('default/index.html.twig', array(
            'currentUser' => $currentUser->getDisplayName(),
            'numberOfDocuments' => count($bookDocuments),
            'appIssue' => $appIssue,
            'bookIssue' => $bookIssue,
            'reviewFixParents' => $reviewFixParents,
            'reviewFixTimes' => $reviewFixTimes,
            'form' => $form->createView(),
            'bookDocuments' => $bookDocuments,
            'queryString' => $queryString,
            'cardsDone' => $board['done'],
            'cardsTodo' => $board['todo'],
        ));
    }

    public function deleteBookDocumentAction(Request $request, int $id)
    {
        $em = $this->getDoctrine()->getManager();
        $bookDocument = $em->getRepository(BookDocument::class)->find($id);

        if (!$bookDocument) {
            throw $this->createNotFoundException('
            Nem található dokumentum ilyen számmal: ' . $id . '!');
        }

        $em->remove($bookDocument);
        $em->flush();

        return $this->redirectToRoute('main');
    }

    /**
     * @return \AppBundle\Utils\JiraCalculator|object
     */
    protected function getJiraCalculator()
    {
        return $this->get('jira_calculator');
    }


}
