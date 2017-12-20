<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BookDocument;
use AppBundle\Entity\JuniorDeveloper;
use AppBundle\Form\BookDocumentType;
use AppBundle\Form\JuniorDeveloperType;
use AppBundle\Utils\GraphCreator;
use AppBundle\Utils\JiraCalculator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        //Dokumentumok
        $currentUser = $this->getUser();
        $documents = $this->get('confluence_api')->getAllDocumentsByCurrentUser($currentUser->getUsername());

        //Lekérdező
        $issueForm = $this->createForm('issue_form');

        //Kimentett issuek
        $reviewFixIssues = $this->getJiraCalculator()->getReviewFixes($currentUser->getUsername(), true);
        $appIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-86', $currentUser->getUsername(), false);
        $bookIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-85', $currentUser->getUsername(), false);

        //Trello
        $board = $this->get('trello_api')->getBoard();

        //Chartok
        $appData = array(
            array('Elkészült taskok', count($board['done'])),
            array('Hátralevő taskok', count($board['todo'])),
        );
        $appChart = $this->getGraphCreator()->createPieGraph($appData, 'pieChart', 'Mérő-app', 'Task');

        $response = $this->render('@App/index.html.twig', array(
            'appIssue' => $appIssue,
            'bookIssue' => $bookIssue,
            'reviewFixIssues' => $reviewFixIssues,
            'cardsDone' => $board['done'],
            'cardsTodo' => $board['todo'],
            'issueForm' => $issueForm->createView(),
            'documents' => $documents,
            'appChart' => $appChart,
        ));
        return $response;
    }

    public function singleTaskAction(string $task, string $user): Response
    {
        $jsonResponse = json_encode($this->getJiraCalculator()->getLoggedTimeOnIssue($task, $user, false, true));
        return new Response($jsonResponse);
    }

    /**
     * @return JiraCalculator
     */
    public function getJiraCalculator(): JiraCalculator
    {
        return $this->get('jira_calculator');
    }

    /**
     * @return GraphCreator
     */
    public function getGraphCreator(): GraphCreator
    {
        return $this->get('graph_creator');
    }
}
