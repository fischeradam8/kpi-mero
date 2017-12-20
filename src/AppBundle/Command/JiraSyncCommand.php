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

            ->setDescription('A review-fixek szinkronizálása')

            ->setHelp('No')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $users =$this->getEm()->getRepository('AppBundle:JuniorDeveloper')->findAll();
        $issues = $this->getEm()->getRepository('AppBundle:JiraIssue')->findAll();
        foreach ($issues as $issue) {
            $this->getEm()->remove($issue);
        }
        $this->getEm()->flush();
        foreach ($users as $user) {
            $this->getJiraCalculator()->getReviewFixes($user->getUsername());
        }
    }

    public function getJiraCalculator(): JiraCalculator
    {
        return $this->getContainer()->get('jira_calculator');
    }

    /**
     * @return \Doctrine\Bundle\DoctrineBundle\Registry|object
     */
    public function getEm()
    {
        return $this->getContainer()->get('doctrine')->getManager();
    }
}
