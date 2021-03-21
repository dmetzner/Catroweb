<?php

namespace App\Api\Services\Base;

use App\Catrobat\Translate\TranslatorAwareTrait;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractApiController extends AbstractController
{
  use TranslatorAwareTrait;

  protected AuthenticationManager $authentication_manager;

  public function __construct(TranslatorInterface $translator, AuthenticationManager $authentication_manager)
  {
    $this->initTranslator($translator);

    $this->authentication_manager = $authentication_manager;
  }

  protected function setDefaultMaxVersionOnNull(?string $max_version): string
  {
    return $max_version ?? '0';
  }

  protected function setDefaultLimitOnNull(?int $limit): int
  {
    return $limit ?? 20;
  }

  protected function setDefaultOffsetOnNull(?int $offset): int
  {
    return $offset ?? 0;
  }

  protected function setDefaultFlavorOnNull(?string $flavor): string
  {
    return $flavor ?? 'pocketcode';
  }

  protected function setDefaultAcceptLanguageOnNull(?string $accept_language): string
  {
    $accept_language = $accept_language ?? 'en';

    try {
      $this->__('category.recent', [], $accept_language);
    } catch (Exception $e) {
      $accept_language = 'en';
    }

    return $accept_language;
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
  protected function addResponseHashToHeaders(array &$responseHeaders, $response): void
  {
    $responseHeaders['X-Response-Hash'] = md5(json_encode($response));
  }
}
