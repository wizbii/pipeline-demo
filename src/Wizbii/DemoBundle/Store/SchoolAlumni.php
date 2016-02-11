<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\BaseStore;

class SchoolAlumni extends BaseStore
{
    const COLLECTION = "school_alumni";

    /**
     * @param Action $action
     * @return DataBag
     */
    public function run($action)
    {
        // validate action
        $schoolId = $action->getProperty("school_id");
        if (!isset($schoolId)) {
            echo "Oups... Missing schoolId in store SchoolAlumni\n";
        }
        $profileId = $action->getProperty("profile_id");
        if (!isset($profileId)) {
            echo "Oups... Missing profileId in store SchoolAlumni\n";
        }

        // update projection
        $schoolAlumni = $this->mongoBasicDao->get(self::COLLECTION, $schoolId);
        if (!isset($schoolAlumni)) {
            $schoolAlumni = ["_id" => $schoolId, "alumni" => []];
        }
        $schoolAlumni["alumni"] = array_unique(array_merge($schoolAlumni["alumni"], [$profileId]));
        $this->mongoBasicDao->put(self::COLLECTION, $schoolAlumni);

        // send asynchrone events
        $eventsConfig = [];
        foreach ($schoolAlumni["alumni"] as $alumni) {
            $eventsConfig[] = new DataBag([
                "profile_id" => $alumni,
                "school_alumni" => $schoolAlumni["alumni"]
            ]);
        }
        return $eventsConfig;
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}