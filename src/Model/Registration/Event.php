<?php

namespace Mr\CventSdk\Model\Registration;

use Mr\Bootstrap\Model\BaseModel;
use Mr\CventSdk\Sdk;

class Event extends BaseModel
{
    public static function getResource()
    {
        return 'event';
    }
}
