<?php

namespace Wizbii\DemoBundle\Mongo;

class MongoBasicDao
{
    const DATABASE = "demo_pipeline";

    /**
     * @var \MongoClient
     */
    protected $mongoClient;

    public function __construct()
    {
        $this->mongoClient = new \MongoClient();
    }

    public function get($collectionName, $id)
    {
        $db = $this->mongoClient->selectDB(self::DATABASE);
        $collection = $db->selectCollection($collectionName);

        return $collection->findOne(["_id" => $id]);
    }

    public function put($collectionName, $array)
    {
        $db = $this->mongoClient->selectDB(self::DATABASE);
        $collection = $db->selectCollection($collectionName);
        $collection->save($array);
        return true;
    }
}