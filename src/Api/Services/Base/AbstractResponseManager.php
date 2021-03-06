<?php

namespace App\Api\Services\Base;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AbstractResponseManager.
 */
abstract class AbstractResponseManager implements TranslatorAwareInterface
{
  use TranslatorAwareTrait;

  public function __construct(TranslatorInterface $translator)
  {
    $this->initTranslator($translator);
  }

  /**
   * The Response hash is added to the header to allow clients to distinguish if the response body must be requested
   * and then loaded. For example, to update a project category shown on a landing page. If the hash between the new
   * and old requests did not change, there is no need to request/load the new response body. This workflow can lead
   * to a significant performance boost.
   *
   * If you are curious why json_encode is used (tldr: It's 2.5x faster than serialize):
   *   - https://stackoverflow.com/questions/2254220/php-best-way-to-md5-multi-dimensional-array
   *
   * @param mixed $response
   */
  public function addResponseHashToHeaders(array &$responseHeaders, $response): void
  {
    $responseHeaders['X-Response-Hash'] = md5(json_encode($response));
  }

  public function addContentLanguageToHeaders(array &$responseHeaders): void
  {
    $responseHeaders['Content-Language'] = $this->getLocale();
  }
}
