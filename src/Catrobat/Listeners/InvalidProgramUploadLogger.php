<?php

namespace App\Catrobat\Listeners;

use App\Catrobat\Events\InvalidProgramUploadedEvent;
use Psr\Log\LoggerInterface;

class InvalidProgramUploadLogger
{
  /**
   * @var LoggerInterface
   */
  private $logger;

  public function __construct(LoggerInterface $logger)
  {
    $this->logger = $logger;
  }

  public function onInvalidProgramUploadedEvent(InvalidProgramUploadedEvent $event)
  {
    $this->logger->error('Invalid File: '.$event->getFile()
      ->getFilename().' Exception: '.$event->getException()
      ->getMessage().' Debug: '.$event->getException()
      ->getDebugMessage());
  }
}
