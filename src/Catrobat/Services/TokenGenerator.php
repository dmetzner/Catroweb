<?php

namespace App\Catrobat\Services;

class TokenGenerator
{
  /**
   * TokenGenerator constructor.
   *
   * @deprecated use JWT tokens
   */
  public function __construct()
  {
  }

  public function generateToken(): string
  {
    return md5(uniqid((string) rand(), false));
  }
}
