<?php

namespace App\Catrobat\Exceptions\Upload;

use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use App\Catrobat\StatusCode;

class OldApplicationVersionException extends InvalidCatrobatFileException
{
  public function __construct($debug)
  {
    parent::__construct('errors.programversion.tooold', StatusCode::OLD_APPLICATION_VERSION, $debug);
  }
}
