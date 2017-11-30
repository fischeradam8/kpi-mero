<?php

namespace AppBundle\Utils;

use AppBundle\Entity\JiraIssue;
use Doctrine\ORM\EntityManager;
use \JiraApiBundle\Service\IssueService;
use \JiraApiBundle\Service\SearchService;

class JiraCalculator
{
    private $jiraIssueApi;
    private $jiraSearchApi;

    public function getLoggedTimeOnIssue(string $issueKey, string $userName): array
    {
        $issue = $this->checkDatabase($issueKey);

        if ($issue) {
            return $issue;
        }

        $issue = $this->jiraIssueApi->get($issueKey);

        if (empty($issue['fields']['subtasks'])) {
            $loggedHours = 0;
            foreach ($issue['fields']['worklog']['worklogs'] as $worklog) {
                if ($worklog['author']['name'] === $userName) {
                    $loggedHours += $worklog['timeSpentSeconds'] / 3600;
                }
            }
            return [
                'name' => $issue['fields']['summary'],
                'key' => $issue['key'],
                'loggedHours' => $loggedHours,
            ];
        }
        $loggedHours = 0;
        foreach ($issue['fields']['subtasks'] as $subIssue) {
            $subtask = $this->jiraIssueApi->get($subIssue['key']);
            foreach ($subtask['fields']['worklog']['worklogs'] as $worklog) {
                //Újrafelhasználás?
                if ($worklog['author']['name'] === $userName) {
                    $loggedHours += $worklog['timeSpentSeconds'] / 3600;
                }
            }
        }

        $savedIssue = new JiraIssue();
        $savedIssue->setName($issue['fields']['summary']);
        $savedIssue->setAssigneeId(1);//TODO később átírni
        $savedIssue->setTaskNumber($issue['key']);
        $savedIssue->setHoursLoggedByAssignee($loggedHours);
        $this->em->persist($savedIssue);
        $this->em->flush();
        return [
            'name' => $issue['fields']['summary'],
            'key' => $issue['key'],
            'loggedHours' => $loggedHours,
        ];
    }

    public function getLoggedTimeOnParent(string $issueKey, string $userName): array
    {
        $subIssue = $this->jiraIssueApi->get($issueKey);
        $parentIssue = $this->jiraIssueApi->get($subIssue['fields']['parent']['key']);
        return $this->getLoggedTimeOnIssue($parentIssue['key'], $userName);
    }

    public function getReviewFixes(string $userName): array
    {
        $reviewFixIssues = $this->jiraSearchApi->search(
            array(
                'jql' => 'assignee="'. $userName .'" and text ~ "review fix"'
            )
        );
        $issues = [];
        $parents = [];
        $reviewFixTimes = [];
        foreach ($reviewFixIssues['issues'] as $reviewFixIssue) {
            $reviewFixTimes[] = $reviewFixIssue['fields']['timespent'] / 3600;
            $parents[] = $this->getLoggedTimeOnParent($reviewFixIssue['key'], $userName);
        }
        $issues['reviewFixTimes'] = $reviewFixTimes;
        $issues['parents'] = $parents;
        return $issues;
    }

    private function checkDatabase(string $issueKey) //TODO id-t is vizsgálni
    {   $task = $this->em->getRepository(JiraIssue::class)->findOneBy(array("taskNumber" => $issueKey));
        if ($task) {
            return array(
                'name' => $task->getName(),
                'key' => $task->getTaskNumber(),
                'loggedHours' => $task->getHoursLoggedByAssignee(),
            );
        };
        return false;
    }

    public function __construct(IssueService $jiraIssueApi, SearchService $jiraSearchApi, EntityManager $em)
    {
        $this->jiraIssueApi = $jiraIssueApi;
        $this->jiraSearchApi = $jiraSearchApi;
        $this->em = $em;
    }
}