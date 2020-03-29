<?php

namespace App\Catrobat\CatrobatCode;

class CodeObject
{
  private $name;

  /**
   * @var array
   */
  private $scripts;

  /**
   * @var array
   */
  private $codeObjects;

  public function __construct()
  {
    $this->scripts = [];
    $this->codeObjects = [];
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @param mixed $name
   */
  public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * @param mixed $scriptsToAdd
   */
  public function addAllScripts($scriptsToAdd)
  {
    foreach ($scriptsToAdd as $script)
    {
      $this->scripts[] = $script;
    }
  }

  public function getCodeObjects(): array
  {
    return $this->codeObjects;
  }

  /**
   * @param mixed $codeObjects
   */
  public function setCodeObjects($codeObjects)
  {
    $this->codeObjects = $codeObjects;
  }

  public function getCodeObjectsRecursively(): array
  {
    $objects = [];
    $objects[] = $this;
    foreach ($this->codeObjects as $object)
    {
      if (null != $object)
      {
        $objects = $this->addObjectsToArray($objects, $object->getCodeObjectsRecursively());
      }
    }

    return $objects;
  }

  /**
   * @param mixed $codeObject
   */
  public function addCodeObject($codeObject)
  {
    $this->codeObjects[] = $codeObject;
  }

  public function getCode(): string
  {
    $code = '';
    foreach ($this->scripts as $script)
    {
      $code .= $script->execute();
    }

    return $code;
  }

  public function getScripts(): array
  {
    return $this->scripts;
  }

  /**
   * @param mixed $objects
   * @param mixed $objectsToAdd
   */
  private function addObjectsToArray($objects, $objectsToAdd): array
  {
    foreach ($objectsToAdd as $object)
    {
      $objects[] = $object;
    }

    return $objects;
  }
}
