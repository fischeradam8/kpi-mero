<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jira_issue")
 * @ORM\Entity()
 */
class JiraIssue
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $taskNumber;

    /**
     * @ORM\Column(type="integer")
     */
    private $assigneeId;

    /**
     * @ORM\Column(type="float")
     */
    private $hoursLoggedByAssignee;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getTaskNumber()
    {
        return $this->taskNumber;
    }

    /**
     * @param mixed $taskNumber
     */
    public function setTaskNumber($taskNumber)
    {
        $this->taskNumber = $taskNumber;
    }

    /**
     * @return mixed
     */
    public function getAssigneeId()
    {
        return $this->assigneeId;
    }

    /**
     * @param mixed $assigneeId
     */
    public function setAssigneeId($assigneeId)
    {
        $this->assigneeId = $assigneeId;
    }

    /**
     * @return mixed
     */
    public function getHoursLoggedByAssignee()
    {
        return $this->hoursLoggedByAssignee;
    }

    /**
     * @param mixed $hoursLoggedByAssignee
     */
    public function setHoursLoggedByAssignee($hoursLoggedByAssignee)
    {
        $this->hoursLoggedByAssignee = $hoursLoggedByAssignee;
    }


}