<?php

namespace App\Api\Services\MediaLibrary;

use App\Api\Services\Base\AbstractApiResponseBuilder;
use App\Entity\MediaPackageFile;
use OpenAPI\Server\Model\MediaFileResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MediaLibraryApiResponseBuilder extends AbstractApiResponseBuilder
{
  private UrlGeneratorInterface $url_generator;

  private ParameterBagInterface $parameter_bag;

  public function __construct(UrlGeneratorInterface $url_generator, ParameterBagInterface $parameter_bag)
  {
    $this->url_generator = $url_generator;
    $this->parameter_bag = $parameter_bag;
  }

  public function buildMediaFilesDataResponse(array $media_package_files): array
  {
    $media_files_data_response = [];

    /** @var MediaPackageFile $media_package_file */
    foreach ($media_package_files as $media_package_file) {
      $media_files_data_response[] = new MediaFileResponse($this->buildMediaFileDataResponse($media_package_file));
    }

    return $media_files_data_response;
  }

  public function buildMediaFileDataResponse(MediaPackageFile $media_package_file): array
  {
    return $mediaFile = [
      'id' => $media_package_file->getId(),
      'name' => $media_package_file->getName(),
      'flavors' => $media_package_file->getFlavorNames(),
      'packages' => $media_package_file->getCategory()->getPackageNames(),
      'category' => $media_package_file->getCategory()->getName(),
      'author' => $media_package_file->getAuthor(),
      'extension' => $media_package_file->getExtension(),
      'download_url' => $this->url_generator->generate(
        'download_media',
        [
          'theme' => $this->parameter_bag->get('umbrellaTheme'),
          'id' => $media_package_file->getId(),
        ],
        UrlGenerator::ABSOLUTE_URL),
    ];
  }
}
