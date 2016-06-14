<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Runnable\DispatcherStore;

class ConversationStore extends DispatcherStore
{
    const COLLECTION = "conversation";

    /**
     * @param Action $action
     */
    public function updateConversationOnMessageCreated($action)
    {
        $conversation = $action->getProperty("conversation");
        $conversation["messages"][$action->getProperty("message_id")] = [
            "message_id"      => $action->getProperty("message_id"),
            "message_content" => $action->getProperty("message_content"),
            "poster_id"       => $action->getProperty("poster_id"),
        ];
        $action->addProperty("conversation", $conversation);
    }

    /**
     * @param Action $action
     */
    public function printInfoOnMessageCreated($action)
    {
        echo $action->getProperty("poster_id") . " has posted : " . $action->getProperty("message_content") . "\n";
    }

    /**
     * @param Action $action
     */
    public function onMessageDeleted($action)
    {
        $conversation = $action->getProperty("conversation");
        unset($conversation["messages"][$action->getProperty("message_id")]);
        $action->addProperty("conversation", $conversation);
    }

    protected function configure()
    {
        $this
            ->newActionMatcher()
                ->ifProperty("conversation_id")->isNotEmpty()
                ->thenExecute("readProjection")
            ->newActionMatcher()
                ->ifActionName()->is("message_created")
                ->ifProperty("conversation")->isNotEmpty()
                ->ifProperty("poster_id")->isNotEmpty()
                ->ifProperty("message_content")->isNotEmpty()
                ->ifProperty("message_id")->isNotEmpty()
                ->thenExecute("updateConversationOnMessageCreated")
                ->thenExecute("printInfoOnMessageCreated")
            ->newActionMatcher()
                ->ifActionName()->is("message_deleted")
                ->ifProperty("user_id")->isNotEmpty()
                ->ifProperty("message_id")->isNotEmpty()
                ->ifProperty("reasons")
                    ->isArray()
                    ->isNotEmpty()
                    ->in(["fix typo", "oops... should not have sent that", "other"])
                ->thenExecute("onMessageDeleted")
            ->executeAfterDispatch("writeProjection")
        ;
    }

    /**
     * @param Action $action
     */
    public function readProjection($action)
    {
        $conversationId = $action->getProperty("conversation_id");
        $conversation = $this->mongoBasicDao->get(self::COLLECTION, $conversationId);
        if (!isset($conversation)) {
            $conversation = [
                "_id" => $conversationId,
                "messages" => []
            ];
        }
        $action->addProperty("conversation", $conversation);
    }

    /**
     * @param Action $action
     */
    public function writeProjection($action)
    {
        $conversation = $action->getProperty("conversation");
        if (isset($conversation)) {
            $this->mongoBasicDao->put(self::COLLECTION, $conversation);
        }
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}