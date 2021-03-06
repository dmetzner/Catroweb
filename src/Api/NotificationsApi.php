<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Notifications\NotificationsApiFacade;
use OpenAPI\Server\Api\NotificationsApiInterface;
use OpenAPI\Server\Model\NotificationsType;
use Symfony\Component\HttpFoundation\Response;

final class NotificationsApi extends AbstractApiController implements NotificationsApiInterface
{
  private NotificationsApiFacade $facade;

  public function __construct(NotificationsApiFacade $facade)
  {
    $this->facade = $facade;
  }

  public function notificationIdReadPut(int $id, ?string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);

    // TODO: Implement notificationIdReadPut() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

  public function notificationsCountGet(&$responseCode = null, array &$responseHeaders = null)
  {
    // TODO: Implement notificationsCountGet() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

  public function notificationsGet(?string $accept_language = null, ?int $limit = 20, ?int $offset = 0, NotificationsType $type = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->getDefaultAcceptLanguageOnNull($accept_language);
    $limit = $this->getDefaultLimitOnNull($limit);
    $offset = $this->getDefaultOffsetOnNull($offset);

    // TODO: Implement notificationsGet() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }

  public function notificationsReadPut(&$responseCode = null, array &$responseHeaders = null)
  {
    // TODO: Implement notificationsReadPut() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return null;
  }
}
