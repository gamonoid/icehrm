<?php
namespace Classes\SystemTasks\DTO;

class Task implements \JsonSerializable
{
    const PRIORITY_TOP = 1000;
    const PRIORITY_ERROR = 100;
    const PRIORITY_WARNING = 50;
    const PRIORITY_INFO = 20;
    const PRIORITY_OK = 10;

    protected $priority;
    protected $text;
    protected $link;
    protected $action;
    protected $details;

    /**
     * Task constructor.
     * @param $priority
     * @param $text
     */
    public function __construct($priority, $text)
    {
        $this->priority = $priority;
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority): Task
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text): Task
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param mixed $link
     * @param string $action
     * @return Task
     */
    public function setLink($link, $action = 'Fix'): Task
    {
        $this->link = $link;
        $this->action = $action;

        return $this;
    }

    /**
     * @param mixed $details
     */
    public function setDetails($details): Task
    {
        $this->details = $details;

        return $this;
    }



    public function jsonSerialize()
    {
        return [
            'priority' => $this->priority,
            'text' => $this->text,
            'link' => $this->link,
            'action' => $this->action,
            'details' => $this->details,
        ];
    }
}
