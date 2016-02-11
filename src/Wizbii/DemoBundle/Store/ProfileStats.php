<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\BaseStore;

class ProfileStats extends BaseStore
{
    const COLLECTION = "profile_stats";

    /**
     * @param Action $action
     * @return DataBag
     */
    public function run($action)
    {
        // validate action
        $profileId = $action->getProperty("profile_id");
        if (!isset($profileId)) {
            echo "Oups... Missing profileId in store ProfileStats\n";
        }

        // update projection
        $stats = $this->mongoBasicDao->get(self::COLLECTION, $profileId);
        if (!isset($stats)) {
            $stats = [
                "_id" => $profileId,
                "schools" => [],
                "network_friends_count" => 0,
                "network_friends_of_friends_count" => 0,
                "network_school_friends_count" => 0,
                "network_count" => 0
            ];
        }
        if ($action->hasProperty("school_id")) {
            $stats["schools"][] = $action->getProperty("school_id");
        }
        if ($action->hasProperty("networks")) {
            $networks = $action->getProperty("networks");
            $stats["network_friends_count"] = count($networks["friends"]);
            $stats["network_friends_of_friends_count"] = count($networks["friends-of-friends"]);
            $stats["network_school_friends_count"] = count($networks["school"]);
            $stats["network_count"] = count(array_unique(array_merge($networks["friends"], $networks["friends-of-friends"], $networks["school"])));
        }
        $this->mongoBasicDao->put(self::COLLECTION, $stats);
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}