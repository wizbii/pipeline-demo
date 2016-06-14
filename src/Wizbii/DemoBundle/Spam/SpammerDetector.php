<?php

namespace Wizbii\DemoBundle\Spam;

use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\DispatcherStore;
use Wizbii\PipelineBundle\Runnable\EventsGenerator\CollectionEventsGenerator;
use Wizbii\PipelineBundle\Runnable\EventsGenerator\EventsGenerator;

class SpammerDetector extends DispatcherStore
{
    /**
     * @param Action $action
     * @return EventsGenerator
     */
    public function onUserMessagesUpdated($action)
    {
        $userMessages = $action->getProperty("user_messages");
        if (count($userMessages["messages"]) > 5) {
            return new CollectionEventsGenerator([new DataBag([
                "spammer_id" => $userMessages["_id"]
            ])]);
        }
    }

    protected function configure()
    {
        $this
            ->newActionMatcher()
                ->ifProperty("user_messages")->isNotEmpty()
                ->thenExecute("onUserMessagesUpdated")
        ;
    }
}