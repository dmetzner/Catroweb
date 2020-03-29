<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;

class ApkRepository
{
  private ?string $dir;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    $apk_dir = $parameter_bag->get('catrobat.apk.dir');
    $dir = preg_replace('/([^\/]+)$/', '$1/', $apk_dir);

    if (!is_dir($dir))
    {
      throw new InvalidStorageDirectoryException($dir.' is not a valid directory');
    }

    $this->dir = $dir;
  }

  public function save(File $file, $id)
  {
    $file->move($this->dir, $this->generateFileNameFromId($id));
  }

  /**
   * @param mixed $id
   */
  public function remove($id)
  {
    $path = $this->dir.$this->generateFileNameFromId($id);
    if (is_file($path))
    {
      unlink($path);
    }
  }

  /**
   * @param mixed $id
   */
  public function getProgramFile($id): File
  {
    return new File($this->dir.$this->generateFileNameFromId($id));
  }

  /**
   * @param mixed $id
   */
  private function generateFileNameFromId($id): string
  {
    return $id.'.apk';
  }
}
