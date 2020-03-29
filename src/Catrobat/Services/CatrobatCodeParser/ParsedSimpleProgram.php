<?php

namespace App\Catrobat\Services\CatrobatCodeParser;

use SimpleXMLElement;

class ParsedSimpleProgram extends ParsedObjectsContainer
{
  /**
   * @var CodeStatistic
   */
  protected $code_statistic;

  public function __construct(SimpleXMLElement $program_xml_properties)
  {
    parent::__construct($program_xml_properties);

    $this->code_statistic = new CodeStatistic();
    $this->computeCodeStatistic();
  }

  public function getCodeStatistic(): CodeStatistic
  {
    return $this->code_statistic;
  }

  public function hasScenes(): bool
  {
    return false;
  }

  protected function computeCodeStatistic()
  {
    $this->code_statistic->update($this);
    $this->code_statistic->computeVariableStatistic($this->xml_properties);
  }
}
