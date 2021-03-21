<?php

namespace App\Api\Services\Base;

use App\Api\Exceptions\ApiException;
use Exception;
use Symfony\Component\HttpFoundation\Response;

trait PandaAuthenticationTrait
{
  private string $token;

  /**
   * @param mixed $value
   *
   * @throws Exception
   */
  public function setPandaAuth($value): void
  {
    $this->token = $this->extractAuthenticationToken($value);
  }

  /**
   * @param mixed $value
   *
   * @throws Exception
   */
  protected function extractAuthenticationToken($value): string
  {
    try {
      return preg_split('#\s+#', $value)[1];
    } catch (Exception $e) {
      throw new ApiException('The route must be registered under the jwt_token_authenticator! (security.yml)', Response::HTTP_UNAUTHORIZED);
    }
  }
}
