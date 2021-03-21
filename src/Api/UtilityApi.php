<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Base\AuthenticationManager;
use App\Api\Services\Utility\UtilityApiLoader;
use App\Api\Services\Utility\UtilityApiResponseBuilder;
use OpenAPI\Server\Api\UtilityApiInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class UtilityApi extends AbstractApiController implements UtilityApiInterface
{
  private UtilityApiLoader $loader;
  private UtilityApiResponseBuilder $response_builder;

  public function __construct(
    TranslatorInterface $translator,
    AuthenticationManager $authentication_manager,
    UtilityApiLoader $loader,
    UtilityApiResponseBuilder $response_builder
  ) {
    parent::__construct($translator, $authentication_manager);

    $this->loader = $loader;
    $this->response_builder = $response_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function healthGet(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function surveyLangCodeGet(string $lang_code, &$responseCode, array &$responseHeaders)
  {
    $survey = $this->loader->getActiveSurvey($lang_code);

    if (null === $survey) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildSurveyResponse($survey);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }
}
