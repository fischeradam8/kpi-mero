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

        $issueService = $this->get('jira_api.issue');
        $issueSearchService = $this->get('jira_api.search');
        $appIssue = $issueService->get('WLOG-599');
        $bookIssue = $issueService->get('WLOG-598');
        $reviewFixIssues = $issueSearchService ->search(
            array(
                'jql' => 'assignee="fischer.adam" and text ~ "review fix"'
            )
        );
        $reviewFixParents = [];
        foreach ($reviewFixIssues['issues'] as $issue) {
            $reviewFixParents[] = $issueService->get($issue['fields']['parent']['key']);
        }

        $developmentTimes= [];
        foreach ($reviewFixParents as $revFixParent) {
            $time = 0;
            foreach ($revFixParent['fields']['subtasks'] as $subtask) {
                $issue = $issueService->get($subtask['key']);
                if ($issue['fields']['issuetype']['name'] == 'Backend' && $issue['fields']['summary'] !== 'Review'&& $issue['fields']['summary'] !== 'Review fix') {
                    $time += $issue['fields']['timespent'];
                }
            }
            $developmentTimes[] = $time;
        }

        return $this->render('default/index.html.twig', array(
            'numberOfDocuments' => count($bookDocuments),
            'appIssue' => $appIssue,
            'bookIssue' => $bookIssue,
            'reviewFixIssues' => $reviewFixIssues,
            'reviewFixParents' => $reviewFixParents,
            'developmentTimes' => $developmentTimes,
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


}
