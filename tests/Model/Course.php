<?php

namespace ilateral\SilverStripe\ImportExport\Tests\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;

//related object
class Course extends DataObject implements TestOnly
{
    private static $db = array(
        "Title" => "Varchar"
    );
}