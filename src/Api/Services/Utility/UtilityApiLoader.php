<?php

namespace App\Api\Services\Utility;

use App\Entity\Survey;
use Doctrine\ORM\EntityManagerInterface;

class UtilityApiLoader
{
  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
  }

  public function getActiveSurvey(string $lang_code): ?Survey
  {
    $survey_repo = $this->entity_manager->getRepository(Survey::class);

    return $survey_repo->findOneBy(['language_code' => $lang_code, 'active' => true]);
  }
}
