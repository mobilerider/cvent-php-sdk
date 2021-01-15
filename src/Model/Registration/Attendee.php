<?php

namespace Mr\CventSdk\Model\Registration;

use Mr\Bootstrap\Model\BaseModel;
use Mr\CventSdk\Sdk;

class Attendee extends BaseModel
{
    public static function getResource()
    {
        return 'attendee';
    }
}
