<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\BaseStore;
use Wizbii\PipelineBundle\Runnable\Store;

class ProfileNetwork extends BaseStore
{
    const COLLECTION = "profile_network";

    /**
     * @param Action $action
     * @return DataBag
     */
    public function run($action)
    {
        // validate action
        $profileId = $action->getProperty("profile_id");
        if (!isset($profileId)) {
            echo "Oups... Missing profileId in store ProfileNetwork\n";
        }

        // update projection
        $profileNetwork = $this->mongoBasicDao->get(self::COLLECTION, $profileId);
        if (!isset($profileNetwork)) {
            $profileNetwork = ["_id" => $profileId, "networks" => [
                "friends" => [],
                "friends-of-friends" => [],
                "school" => []
            ]];
        }
        if ($action->hasProperty("school_network")) {
            $profileNetwork["networks"]["school"] = $action->getProperty("school_network");
        }
        $this->mongoBasicDao->put(self::COLLECTION, $profileNetwork);

        // update action
        $action->addProperty("networks", $profileNetwork["networks"]);
        $action->removeProperty("school_network");
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}