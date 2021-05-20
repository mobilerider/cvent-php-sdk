<?php

namespace Mr\CventSdk\Repository\Registration;

use Mr\Bootstrap\Http\Filtering\MrApiQueryBuilder;
use Mr\Bootstrap\Interfaces\HttpDataClientInterface;
use Mr\Bootstrap\Repository\BaseRepository;
use Mr\CventSdk\Model\Registration\Event;
use Mr\CventSdk\Sdk;

class EventRepository extends BaseRepository
{
    public function __construct(HttpDataClientInterface $client, array $options = [])
    {
        $options["queryBuilderClass"] = MrApiQueryBuilder::class;
        parent::__construct($client, $options);
    }
    protected function getResourcePath()
    {
        return Sdk::API_VERSION . mr_plural($this->getResource());
    }

    public function getModelClass()
    {
        return Event::class;
    }

    public function parseOne(array $data, array &$metadata = [])
    {
        return $data;
    }

    public function parseMany(array $data, array &$metadata = [])
    {
        if (!$data) {
            return [];
        }
        
        $metadata = $data["paging"] ?? [];

        return $data["data"] ?? [];
    }

    protected function buildQuery(array $filters, array $params)
    {
        return $filters + $params;
    }
}
