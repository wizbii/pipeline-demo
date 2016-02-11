<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\BaseStore;

class ProfileSearch extends BaseStore
{
    const COLLECTION = "profile_search";

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
        $profileSearch = $this->mongoBasicDao->get(self::COLLECTION, $profileId);
        if (!isset($profileSearch)) {
            $profileSearch = [
                "_id" => $profileId,
                "schools" => [],
                "network_friends" => [],
                "network_friends_of_friends" => [],
                "network_school_friends" => [],
            ];
        }
        if ($action->hasProperty("networks")) {
            $networks = $action->getProperty("networks");
            $profileSearch["network_friends"] = $networks["friends"];
            $profileSearch["network_friends_of_friends"] = $networks["friends-of-friends"];
            $profileSearch["network_school_friends"] = $networks["school"];
        }
        $this->mongoBasicDao->put(self::COLLECTION, $profileSearch);
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}