<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\DispatcherStore;
use Wizbii\PipelineBundle\Runnable\Store;

class ProfileIdentityCard extends DispatcherStore
{
    const COLLECTION = "identity_card";

    /**
     * @param Action $action
     */
    public function onNewEducation($action)
    {
        $action->getProperty("identity_card")["school_name"] = $action->getProperty("school_id");
    }

    /**
     * @param Action $action
     */
    public function onNetworkUpdated($action)
    {
        $networks = $action->getProperty("networks");
        $action->getProperty("identity_card")["network_count"] = count(array_unique(array_merge($networks["friends"], $networks["friends-of-friends"], $networks["school"])));
    }

    /**
     * @param Action $action
     */
    public function readProjection($action)
    {
        $profileId = $action->getProperty("profile_id");
        $action->addProperty("identity_card", $this->mongoBasicDao->get(self::COLLECTION, $profileId));
    }

    /**
     * @param Action $action
     */
    public function createIfNew($action)
    {
        $identityCard = $action->getProperty("identity_card");
        // create it if it does not exist
        if (!isset($identityCard)) {
            $identityCard = ["_id" => $action->getProperty("profile_id")];
        }
        $action->addProperty("identity_card", $identityCard);
    }

    /**
     * @param Action $action
     */
    public function writeProjection($action)
    {
        $this->mongoBasicDao->put(self::COLLECTION, $action->getProperty("identity_card"));
    }

    protected function configure()
    {
        $this->executeBeforeDispatch("readProjection")
             ->executeBeforeDispatch("createIfNew")
             ->executeAfterDispatch("writeProjection")
             ->newActionMatcher()
                 ->ifActionName()->is("profile_studied_in_school")
                 ->ifProperty("profile_id")->isNotEmpty()
                 ->ifProperty("school_id")->isNotEmpty()
                 ->thenExecute("onNewEducation")
             ->newActionMatcher()
                 ->ifActionName()->is("profile_studied_in_school")
                 ->ifProperty("profile_id")->isNotEmpty()
                 ->ifProperty("school_id")->isEmpty()
                 ->thenExecute("onNewEducationWithNeo4j")
             ->newActionMatcher()
                 ->ifProperty("networks")
                    ->isArray()
                    ->isNotEmpty()
                    ->containsKeys(["school", "friends", "friends-of-friends"])
                 ->thenExecute("onNetworkUpdated")
        ;
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}