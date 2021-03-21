<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Base\AuthenticationManager;
use App\Api\Services\Base\PandaAuthenticationTrait;
use App\Api\Services\Projects\ProjectsApiLoader;
use App\Api\Services\Projects\ProjectsApiProcessor;
use App\Api\Services\Projects\ProjectsApiResponseBuilder;
use App\Api\Services\Projects\ProjectsApiValidator;
use App\Catrobat\Requests\AddProgramRequest;
use App\Catrobat\Translate\TranslatorAwareTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OpenAPI\Server\Api\ProjectsApiInterface;
use OpenAPI\Server\Model\ProjectReportRequest;
use OpenAPI\Server\Model\UploadErrorResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsApi extends AbstractApiController implements ProjectsApiInterface
{
  use PandaAuthenticationTrait;
  use TranslatorAwareTrait;

  private ProjectsApiLoader $loader;
  private ProjectsApiProcessor $processor;
  private ProjectsApiResponseBuilder $response_builder;
  private ProjectsApiValidator $validator;

  private EntityManagerInterface $entity_manager;
  private RequestStack $request_stack;

  public function __construct(
    TranslatorInterface $translator,
    AuthenticationManager $authentication_manager,
    ProjectsApiLoader $projects_api_loader,
    ProjectsApiProcessor $projects_api_processor,
    ProjectsApiResponseBuilder $response_builder,
    ProjectsApiValidator $validator,
    EntityManagerInterface $entity_manager,
    RequestStack $request_stack
  ) {
    parent::__construct($translator, $authentication_manager);

    $this->processor = $projects_api_processor;
    $this->loader = $projects_api_loader;
    $this->response_builder = $response_builder;
    $this->validator = $validator;

    $this->initTranslator($translator);

    $this->entity_manager = $entity_manager;
    $this->request_stack = $request_stack;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectIdGet(string $id, &$responseCode, array &$responseHeaders)
  {
    $project = $this->loader->findProjectByID($id);
    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildProjectDataResponse($project);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsFeaturedGet(string $platform = null, string $max_version = null, ?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode = null, array &$responseHeaders = null): array
  {
    $max_version = $this->setDefaultMaxVersionOnNull($max_version);
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);

    $featured_projects = $this->loader->getFeaturedProjects($flavor, $limit, $offset, $platform, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildFeaturedProjectsResponse($featured_projects);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsGet(string $category, ?string $accept_language = null, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null): array
  {
    $max_version = $this->setDefaultMaxVersionOnNull($max_version);
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);
    $accept_language = $this->setDefaultAcceptLanguageOnNull($accept_language);
    $flavor = $this->setDefaultFlavorOnNull($flavor);

    $user = $this->authentication_manager->getAuthenticatedUser();
    $projects = $this->loader->getProjectsFromCategory($category, $max_version, $limit, $offset, $flavor, $user);

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildProjectsDataResponse($projects);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectIdRecommendationsGet(string $id, string $category, ?string $accept_language = null, string $max_version = null, ?int $limit = 20, ?int $offset = 0, string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->setDefaultMaxVersionOnNull($max_version);
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);
    $accept_language = $this->setDefaultAcceptLanguageOnNull($accept_language);
    $flavor = $this->setDefaultFlavorOnNull($flavor);

    $project = $this->loader->findProjectByID($id, true);
    if (null === $project) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $recommended_projects = $this->loader->getRecommendedProjects($id, $category, $max_version, $limit, $offset, $flavor, $this->authentication_manager->getAuthenticatedUser());

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildProjectsDataResponse($recommended_projects);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectsPost(string $checksum, UploadedFile $file, ?string $accept_language = null, ?string $flavor = null, ?bool $private = false, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->setDefaultAcceptLanguageOnNull($accept_language);
    $flavor = $this->setDefaultFlavorOnNull($flavor);
    $private = $private ?? false;

    $validation_wrapper = $this->validator->validateUploadFile($checksum, $file, $accept_language);
    if ($validation_wrapper->hasError()) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $error = $validation_wrapper->getError();
      $error_response = $this->response_builder->buildProjectUploadErrorResponse($error);
      $this->addResponseHashToHeaders($responseHeaders, $error);

      return $error_response;
    }

    // Getting the user who uploaded
    $user = $this->authentication_manager->getAuthenticatedUser();

    // Needed (for tests) to make sure everything is up to date (followers, ..)
    $this->entity_manager->refresh($user);

    try {
      $add_project_request = new AddProgramRequest($user, $file, $this->request_stack->getCurrentRequest()->getClientIp(), $accept_language, $flavor);
      $project = $this->processor->addProject($add_project_request);
    } catch (Exception $e) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return new UploadErrorResponse(['error' => $this->__('api.projectsPost.creating_error', [], $accept_language)]);
    }

    // Setting the program's attributes
    $project->setPrivate($private);
    $this->entity_manager->flush();

    // Since we have come this far, the project upload is completed
    $responseCode = Response::HTTP_CREATED;
    $responseHeaders['Location'] = $this->response_builder->buildProjectLocation($project);

    return null;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsSearchGet(string $query, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->setDefaultMaxVersionOnNull($max_version);
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);
    $flavor = $this->setDefaultFlavorOnNull($flavor);

    $programs = $this->loader->searchProjects($query, $limit, $offset, $max_version, $flavor);

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildProjectsDataResponse($programs);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsCategoriesGet(?string $max_version = null, ?string $flavor = null, ?string $accept_language = null, &$responseCode = null, array &$responseHeaders = null): array
  {
    $max_version = $this->setDefaultMaxVersionOnNull($max_version);
    $accept_language = $this->setDefaultAcceptLanguageOnNull($accept_language);
    $limit = $this->setDefaultLimitOnNull(null);
    $offset = $this->setDefaultOffsetOnNull(null);
    $flavor = $this->setDefaultFlavorOnNull($flavor);

    $response = [];
    $categories = ['recent', 'random', 'most_viewed', 'most_downloaded', 'example', 'scratch', 'recommended'];
    $user = $this->authentication_manager->getAuthenticatedUser();

    foreach ($categories as $category) {
      $projects = $this->loader->getProjectsFromCategory($category, $max_version, $limit, $offset, $flavor, $user);
      $response[] = $this->response_builder->buildProjectCategoryResponse($projects, $category, $accept_language);
    }

    $responseCode = Response::HTTP_OK;
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsUserGet(?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->setDefaultMaxVersionOnNull($max_version);
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);
    $flavor = $this->setDefaultFlavorOnNull($flavor);

    $user = $this->authentication_manager->getUserFromAuthenticationToken($this->token);
    if (null === $user) {
      $responseCode = Response::HTTP_FORBIDDEN;

      return null;
    }

    $user_projects = $this->loader->getUserProjects($user->getUsername(), $limit, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildProjectsDataResponse($user_projects);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function projectsUserIdGet(string $id, ?string $max_version = null, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $max_version = $this->setDefaultMaxVersionOnNull($max_version);
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);
    $flavor = $this->setDefaultFlavorOnNull($flavor);

    if (!$this->validator->validateUserExists($id)) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $projects = $this->loader->getUserPublicPrograms($id, $limit, $offset, $flavor, $max_version);

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildProjectsDataResponse($projects);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function projectIdReportPost(string $id, ProjectReportRequest $project_report_request, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement projectIdReportPost() method.
  }
}
