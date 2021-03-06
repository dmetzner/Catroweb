<?php

namespace App\Catrobat\Services;

use App\Catrobat\Exceptions\Upload\InvalidArchiveException;
use App\Utils\TimeUtils;
use App\Utils\Utils;
use Exception;
use Symfony\Component\HttpFoundation\File\File;
use ZipArchive;

class CatrobatFileExtractor
{
  private string $extract_dir;

  private string $extract_path;

  public function __construct(string $extract_dir, string $extract_path)
  {
    Utils::verifyDirectoryExists($extract_dir);
    $this->extract_dir = $extract_dir;
    $this->extract_path = $extract_path;
  }

  /**
   * @throws Exception
   */
  public function extract(File $file): ExtractedCatrobatFile
  {
    $temp_path = hash('md5', TimeUtils::getTimestamp().random_int(0, mt_getrandmax()));
    $full_extract_dir = $this->extract_dir.$temp_path.'/';
    $full_extract_path = $this->extract_path.$temp_path.'/';

    $zip = new ZipArchive();
    $res = $zip->open($file->getPathname());

    if (true === $res) {
      $zip->extractTo($full_extract_dir);
      $zip->close();
    } else {
      throw new InvalidArchiveException();
    }

    return new ExtractedCatrobatFile($full_extract_dir, $full_extract_path, $temp_path);
  }

  public function getExtractDir(): string
  {
    return $this->extract_dir;
  }

  public function getExtractPath(): string
  {
    return $this->extract_path;
  }
}
