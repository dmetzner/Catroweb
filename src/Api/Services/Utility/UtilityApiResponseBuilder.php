<?php

namespace App\Api\Services\Utility;

use App\Entity\Survey;
use OpenAPI\Server\Model\SurveyResponse;

class UtilityApiResponseBuilder
{
  public function buildSurveyResponse(Survey $survey): SurveyResponse
  {
    return new SurveyResponse([
      'url' => $survey->getUrl(),
    ]);
  }
}
