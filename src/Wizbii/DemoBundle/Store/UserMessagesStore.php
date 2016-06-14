<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Runnable\DispatcherStore;

class UserMessagesStore extends DispatcherStore
{
    const COLLECTION = "user_messages";

    /**
     * @param Action $action
     */
    public function onMessageCreated($action)
    {
        $userMessages = $action->getProperty("user_messages");
        $userMessages["messages"][$action->getProperty("message_id")] = [
            "message_id"      => $action->getProperty("message_id"),
            "message_content" => $action->getProperty("message_content"),
        ];
        $action->addProperty("user_messages", $userMessages);
    }

    /**
     * @param Action $action
     */
    public function onMessageDeleted($action)
    {
        $userMessages = $action->getProperty("user_messages");
        unset($userMessages["messages"][$action->getProperty("message_id")]);
        $action->addProperty("user_messages", $userMessages);
    }

    protected function configure()
    {
        $this
            ->newActionMatcher()
                ->ifProperty("conversation_id")->isNotEmpty()
                ->thenExecute("readProjection")
            ->newActionMatcher()
                ->ifActionName()->is("message_created")
                ->ifProperty("conversation_id")->isNotEmpty()
                ->ifProperty("poster_id")->isNotEmpty()
                ->ifProperty("message_content")->isNotEmpty()
                ->ifProperty("message_id")->isNotEmpty()
                ->thenExecute("onMessageCreated")
            ->newActionMatcher()
                ->ifActionName()->is("message_deleted")
                ->ifProperty("user_id")->isNotEmpty()
                ->ifProperty("message_id")->isNotEmpty()
                ->thenExecute("onMessageDeleted")
            ->executeAfterDispatch("writeProjection")
        ;
    }

    /**
     * @param Action $action
     */
    public function readProjection($action)
    {
        $userId = $action->getProperty("poster_id");
        $userMessages = $this->mongoBasicDao->get(self::COLLECTION, $userId);
        if (!isset($userMessages)) {
            $userMessages = [
                "_id" => $userId,
                "messages" => []
            ];
        }
        $action->addProperty("user_messages", $userMessages);
    }

    /**
     * @param Action $action
     */
    public function writeProjection($action)
    {
        $userMessages = $action->getProperty("user_messages");
        if (isset($userMessages)) {
            $this->mongoBasicDao->put(self::COLLECTION, $userMessages);
        }
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}