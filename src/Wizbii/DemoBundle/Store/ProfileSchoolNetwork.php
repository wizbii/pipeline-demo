<?php

namespace Wizbii\DemoBundle\Store;

use Wizbii\DemoBundle\Mongo\MongoBasicDao;
use Wizbii\PipelineBundle\Model\Action;
use Wizbii\PipelineBundle\Model\DataBag;
use Wizbii\PipelineBundle\Runnable\BaseStore;

class ProfileSchoolNetwork extends BaseStore
{
    const COLLECTION = "profile_school_network";

    /**
     * @param Action $action
     * @return DataBag
     */
    public function run($action)
    {
        // validate action
        $schoolAlumni = $action->getProperty("school_alumni");
        if (!isset($schoolAlumni)) {
            echo "Oups... Missing schoolAlumni in store ProfileSchoolNetwork\n";
        }
        $profileId = $action->getProperty("profile_id");
        if (!isset($profileId)) {
            echo "Oups... Missing profileId in store ProfileSchoolNetwork\n";
        }

        // update projection
        $profileSchoolNetwork = $this->mongoBasicDao->get(self::COLLECTION, $profileId);
        if (!isset($profileSchoolNetwork)) {
            $profileSchoolNetwork = ["_id" => $profileId, "network" => []];
        }
        if ($action->getName() === "school_alumni_updated") {
            $this->updateProjectionWhenSchoolAlumniAreUpdated($profileSchoolNetwork, $schoolAlumni, $profileId);
        }
        else if ($action->getName() === "school_aliases_updated") {
            $this->updateProjectionWhenSchoolAliasesAreUpdated($profileSchoolNetwork, $profileId);
        }
        $this->mongoBasicDao->put(self::COLLECTION, $profileSchoolNetwork);

        // update action
        $action->addProperty("school_network", $profileSchoolNetwork["network"]);
    }

    public function updateProjectionWhenSchoolAlumniAreUpdated(&$profileSchoolNetwork, $schoolAlumni, $profileId)
    {
        $schoolNetwork = array_values(array_unique(array_merge($profileSchoolNetwork["network"], $schoolAlumni)));
        $profileSchoolNetwork["network"] = array_values(array_diff($schoolNetwork, [$profileId]));
    }

    public function updateProjectionWhenSchoolAliasesAreUpdated(&$profileSchoolNetwork)
    {
        // use graph database to compute this value.
        // This is outside of this demo scope.
    }

    /**
     * @var MongoBasicDao
     */
    public $mongoBasicDao;

}