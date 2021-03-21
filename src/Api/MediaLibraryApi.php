<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Base\AuthenticationManager;
use App\Api\Services\MediaLibrary\MediaLibraryApiResponseBuilder;
use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackage;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use App\Utils\APIQueryHelper;
use Doctrine\ORM\EntityManagerInterface;
use OpenAPI\Server\Api\MediaLibraryApiInterface;
use OpenAPI\Server\Model\MediaFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class MediaLibraryApi extends AbstractApiController implements MediaLibraryApiInterface
{
  private MediaLibraryApiResponseBuilder $response_builder;
  private EntityManagerInterface $entity_manager;
  private MediaPackageFileRepository $media_package_file_repository;
  private RequestStack $stack;

  public function __construct(
    TranslatorInterface $translator,
    AuthenticationManager $authentication_manager,
    MediaLibraryApiResponseBuilder $response_builder,

    EntityManagerInterface $entity_manager,
    MediaPackageFileRepository $media_package_file_repository, RequestStack $stack)
  {
    parent::__construct($translator, $authentication_manager);

    $this->response_builder = $response_builder;

    $this->entity_manager = $entity_manager;
    $this->media_package_file_repository = $media_package_file_repository;
    $this->stack = $stack;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesSearchGet(string $query, ?string $flavor = null, ?int $limit = 20, ?int $offset = 0, ?string $package_name = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);

    $found_media_files = $this->media_package_file_repository->search($query, $flavor, $package_name, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildMediaFilesDataResponse($found_media_files);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaPackageNameGet(string $name, ?int $limit = 20, ?int $offset = 0, &$responseCode = null, array &$responseHeaders = null)
  {
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);

    $media_package = $this->entity_manager->getRepository(MediaPackage::class)
      ->findOneBy(['nameUrl' => $name])
    ;

    if (null === $media_package) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    $media_package_categories = $media_package->getCategories();
    if (empty($media_package_categories)) {
      return [];
    }

    $json_response_array = [];

    /** @var MediaPackageCategory $media_package_category */
    foreach ($media_package_categories as $media_package_category) {
      $media_package_files = $media_package_category->getFiles();
      if ((0 != $offset && count($media_package_files) <= $offset) || count($json_response_array) === $limit) {
        if (0 != $offset) {
          $offset -= count($media_package_files);
        }
        continue;
      }

      /** @var MediaPackageFile $media_package_file */
      foreach ($media_package_files as $media_package_file) {
        if (0 != $offset) {
          --$offset;
          continue;
        }
        if (count($json_response_array) === $limit) {
          break;
        }
        $json_response_array[] = new MediaFileResponse($this->response_builder->buildMediaFileDataResponse($media_package_file));
      }
    }

    return $json_response_array;
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFileIdGet(int $id, &$responseCode = null, array &$responseHeaders = null)
  {
    $media_package_file = $this->entity_manager->getRepository(MediaPackageFile::class)
      ->findOneBy(['id' => $id])
    ;

    if (null === $media_package_file) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;

    return new MediaFileResponse($this->response_builder->buildMediaFileDataResponse($media_package_file));
  }

  /**
   * {@inheritdoc}
   */
  public function mediaFilesGet(?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);

    $qb = $this->entity_manager->createQueryBuilder()
      ->select('f')
      ->from('App\Entity\MediaPackageFile', 'f')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;
    APIQueryHelper::addFileFlavorsCondition($qb, $flavor, 'f');
    $media_package_files = $qb->getQuery()->getResult();

    if (null === $media_package_files) {
      $response = [];
    } else {
      $response = $this->response_builder->buildMediaFilesDataResponse($media_package_files);
    }

    $responseCode = Response::HTTP_OK;
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }
}
