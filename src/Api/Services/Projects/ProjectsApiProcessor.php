<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiProcessor;
use App\Catrobat\Requests\AddProgramRequest;
use App\Entity\Program;
use App\Entity\ProgramManager;
use Exception;

class ProjectsApiProcessor extends AbstractApiProcessor
{
  private ProgramManager $project_manager;

  public function __construct(ProgramManager $project_manager)
  {
    $this->project_manager = $project_manager;
  }

  /**
   * @throws Exception
   */
  public function addProject(AddProgramRequest $add_program_request): ?Program
  {
    return $this->project_manager->addProgram($add_program_request);
  }
}
