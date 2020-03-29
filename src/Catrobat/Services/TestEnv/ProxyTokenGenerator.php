<?php

namespace App\Catrobat\Services\TestEnv;

use App\Catrobat\Services\TokenGenerator;

class ProxyTokenGenerator extends TokenGenerator
{
  /**
   * @var TokenGenerator
   */
  private $generator;

  public function __construct(TokenGenerator $default_generator)
  {
    parent::__construct();
    $this->generator = $default_generator;
  }

  public function generateToken(): string
  {
    return $this->generator->generateToken();
  }

  public function setTokenGenerator(TokenGenerator $generator)
  {
    $this->generator = $generator;
  }
}
