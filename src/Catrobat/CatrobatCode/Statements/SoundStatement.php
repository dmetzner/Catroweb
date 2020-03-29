<?php

namespace App\Catrobat\CatrobatCode\Statements;

class SoundStatement extends Statement
{
  private $value;

  private $fileName;

  private $name;

  /**
   * SoundStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $value
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $value)
  {
    $this->value = $value;
    parent::__construct($statementFactory, $xmlTree, $spaces,
      $value,
      '');
  }

  public function execute(): string
  {
    $code = $this->value;
    $this->findNames();

    if (null != $this->name)
    {
      $code .= $this->name->execute();
    }

    if (null != $this->fileName)
    {
      $code .= ' (filename: '.$this->fileName->execute().')';
    }

    return $code;
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }

  private function findNames()
  {
    $tmpStatements = parent::getStatements();
    foreach ($tmpStatements as $statement)
    {
      if (null != $statement)
      {
        if ($statement instanceof ValueStatement)
        {
          $this->name = $statement;
        }
        else
        {
          if ($statement instanceof FileNameStatement)
          {
            $this->fileName = $statement;
          }
        }
      }
    }
  }
}
