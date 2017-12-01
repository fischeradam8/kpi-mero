<?php

namespace AppBundle\Command;

use AppBundle\Utils\JiraCalculator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JiraSyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('app:jira-sync')

            ->setDescription('Syncronizes the Jira issues with the local database.')

            ->setHelp('No')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $issuesToSync = $this->getEm()->getRepository('AppBundle:JiraIssue')->findAll();
        foreach ($issuesToSync as $issue) {
            $this->getJiraCalculator()->getLoggedTimeOnIssue($issue->getKey(),'fischer.adam', false);
        }
    }

    public function getJiraCalculator(): JiraCalculator
    {
        return $this->getContainer()->get('jira_calculator');
    }

    public function getEm()
    {
        return $this->getContainer()->get('doctrine');
    }
}