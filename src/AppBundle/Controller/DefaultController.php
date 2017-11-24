<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BookDocument;
use AppBundle\Form\BookDocumentType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    public function indexAction(Request $request)
    {
        //Repository
        $queryString = $request->getQueryString();

        $em = $this->getDoctrine()->getManager();
        $query= $em->createQuery(
            'SELECT book_document FROM AppBundle:BookDocument book_document'
        );
        $bookDocuments = $query->getResult();

        $newDocument = new BookDocument();
        $form = $this->createFormBuilder($newDocument)
            ->add('name', 'text')
            ->add('url', 'text')
            ->add('save', 'submit', array('label' => 'Új felvétele'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newDocument = $form->getData();
            $newDocument->setAuthor('Fischer Ádám');
            $em->persist($newDocument);
            $em->flush();

            return $this->redirectToRoute('main');
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            return $this->redirectToRoute('main',
                array('urlInvalid' => true)
            );
        }

        $reviewFixIssues = $this->getJiraCalculator()->getReviewFixes('fischer.adam');
        $reviewFixParents= $reviewFixIssues['parents'];
        $reviewFixTimes = $reviewFixIssues['reviewFixTimes'];
        $appIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-86', 'fischer.adam');
        $bookIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-87', 'fischer.adam');
        $boards = $this->get('trello_api')->getBoard();
        return $this->render('default/index.html.twig', array(
            'numberOfDocuments' => count($bookDocuments),
            'appIssue' => $appIssue,
            'bookIssue' => $bookIssue,
            'reviewFixParents' => $reviewFixParents,
            'reviewFixTimes' => $reviewFixTimes,
            'form' => $form->createView(),
            'bookDocuments' => $bookDocuments,
            'queryString' => $queryString,
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
