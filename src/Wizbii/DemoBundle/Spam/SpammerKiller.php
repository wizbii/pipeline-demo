<?php

namespace Wizbii\DemoBundle\Spam;

use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\DispatcherStore;
use Wizbii\PipelineBundle\Runnable\EventsGenerator\CollectionEventsGenerator;
use Wizbii\PipelineBundle\Runnable\EventsGenerator\EventsGenerator;

class SpammerKiller extends DispatcherStore
{
    /**
     * @param Action $action
     * @return EventsGenerator
     */
    public function onSpammerDetected($action)
    {
        echo "going to kill spammer '"  . $action->getProperty("spammer_id") . "'\n";
    }

    protected function configure()
    {
        $this
            ->newActionMatcher()
                ->ifProperty("spammer_id")->isNotEmpty()
                ->thenExecute("onSpammerDetected")
        ;
    }
}