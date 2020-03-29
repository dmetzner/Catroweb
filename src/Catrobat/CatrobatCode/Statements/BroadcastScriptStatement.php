<?php

namespace App\Catrobat\CatrobatCode\Statements;

class BroadcastScriptStatement extends Statement
{
  const BEGIN_STRING = 'when receive message ';

  private $message;

  /**
   * BroadcastScriptStatement constructor.
   *
   * @param mixed $statementFactory
   * @param mixed $xmlTree
   * @param mixed $spaces
   */
  public function __construct($statementFactory, $xmlTree, $spaces)
  {
    parent::__construct($statementFactory, $xmlTree, $spaces,
      self::BEGIN_STRING,
      '');
  }

  public function execute(): string
  {
    $children = $this->executeChildren();
    $code = parent::addSpaces().self::BEGIN_STRING;
    if (null != $this->message)
    {
      $code .= $this->message->execute();
    }
    $code .= '<br/>'.$children;

    return $code;
  }

  public function executeChildren(): string
  {
    $code = '';
    foreach ($this->statements as $value)
    {
      if ($value instanceof ReceivedMessageStatement)
      {
        $this->message = $value;
      }
      else
      {
        $code .= $value->execute();
      }
    }

    return $code;
  }

  /**
   * @return mixed
   */
  public function getMessage()
  {
    if (null == $this->message)
    {
      $this->message = $this->xmlTree->receivedMessage;
    }

    return $this->message;
  }
}
