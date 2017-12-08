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

        $currentUser = $this->getUser();
        $documents = $this->get('confluence_api')->getAllDocumentsByCurrentUser($currentUser->getUsername());
        $issueForm = $this->createForm('issue_form');
        $reviewFixIssues = $this->getJiraCalculator()->getReviewFixes($currentUser->getUsername(), true);
        $appIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-86', $currentUser->getUsername(), false);
        $bookIssue = $this->getJiraCalculator()->getLoggedTimeOnIssue('TELWS-85', $currentUser->getUsername(), false);
        $board = $this->get('trello_api')->getBoard();

        $response = $this->render('@App/default/index.html.twig', array(
            'currentUser' => $currentUser->getDisplayName(),
            'appIssue' => $appIssue,
            'bookIssue' => $bookIssue,
            'reviewFixIssues' => $reviewFixIssues,
            'cardsDone' => $board['done'],
            'cardsTodo' => $board['todo'],
            'issueForm' => $issueForm->createView(),
            'documents' => $documents,
        ));
        return $response;
    }

    public function singleTaskAction(string $task, string $user): Response
    {
        $jsonResponse = json_encode($this->getJiraCalculator()->getLoggedTimeOnIssue($task, $user, false, true));
        return new Response($jsonResponse);
    }

    /**
     * @return \AppBundle\Utils\JiraCalculator|object
     */
    protected function getJiraCalculator()
    {
        return $this->get('jira_calculator');
    }


}
