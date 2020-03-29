<?php

namespace App\Catrobat\CatrobatCode\Statements;

class FormulaStatement extends Statement
{
  /**
   * @var Statement
   */
  private $leftChild;
  /**
   * @var Statement
   */
  private $rightChild;
  /**
   * @var Statement
   */
  private $type;

  private $category;

  /**
   * FormulaStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   * @param mixed $category
   */
  public function __construct($statementFactory, $xmlTree, $spaces, $category)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      '', '');
    $this->category = $category;
  }

  public function execute(): string
  {
    return $this->executeChildren();
  }

  public function executeChildren(): string
  {
    $code = '';
    $endCode = '';

    $this->setVariables();

    if (null != $this->type)
    {
      $code .= $this->type->execute();
    }
    if (null != $this->type && (null != $this->leftChild || null != $this->rightChild))
    {
      $code .= '(';
      $endCode = ')';
    }

    if (null != $this->leftChild)
    {
      $code .= $this->leftChild->execute();
    }

    if (null != $this->leftChild && null != $this->rightChild)
    {
      $code .= ', ';
    }

    if (null != $this->rightChild)
    {
      $code .= $this->rightChild->execute();
    }

    return $code.$endCode;
  }

  /**
   * @return mixed
   */
  public function getCategory()
  {
    return $this->category;
  }

  protected function setVariables()
  {
    foreach ($this->statements as $value)
    {
      if ($value instanceof LeftChildStatement)
      {
        $this->leftChild = $value;
      }
      else
      {
        if ($value instanceof RightChildStatement)
        {
          $this->rightChild = $value;
        }
        else
        {
          if ($value instanceof ValueStatement)
          {
            $this->type = $value;
          }
        }
      }
    }
  }
}
