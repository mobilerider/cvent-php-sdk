<?php


namespace Mr\CventSdk\Repository\Registration;

use Mr\CventSdk\Model\Registration\Attendee;
use Mr\Bootstrap\Http\Filtering\MrApiQueryBuilder;
use Mr\Bootstrap\Interfaces\HttpDataClientInterface;
use Mr\Bootstrap\Repository\BaseRepository;
use Mr\CventSdk\Sdk;

class AttendeeRepository extends BaseRepository
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
        return Attendee::class;
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
