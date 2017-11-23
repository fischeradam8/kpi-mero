<?php

namespace AppBundle\Utils;

use \JiraApiBundle\Service\IssueService;
use \JiraApiBundle\Service\SearchService;

class JiraCalculator
{
    private $jiraIssueApi;
    private $jiraSearchApi;

    public function getLoggedTimeOnIssue(string $issueKey, string $userName): array
    {
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

    public function __construct(IssueService $jiraIssueApi, SearchService $jiraSearchApi)
    {
        $this->jiraIssueApi = $jiraIssueApi;
        $this->jiraSearchApi = $jiraSearchApi;
    }
}