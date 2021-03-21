<?php

namespace App\Api;

use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Base\AuthenticationManager;
use App\Api\Services\Base\PandaAuthenticationTrait;
use App\Api\Services\User\UserApiLoader;
use App\Api\Services\User\UserApiProcessor;
use App\Api\Services\User\UserApiResponseBuilder;
use App\Api\Services\User\UserApiValidator;
use Exception;
use OpenAPI\Server\Api\UserApiInterface;
use OpenAPI\Server\Model\ExtendedUserDataResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserApi extends AbstractApiController implements UserApiInterface
{
  use PandaAuthenticationTrait;

  private UserApiValidator $validator;
  private UserApiProcessor $processor;
  private UserApiLoader $loader;
  private UserApiResponseBuilder $response_builder;

  public function __construct(
    TranslatorInterface $translator,
    AuthenticationManager $authentication_manager,
    UserApiValidator $user_api_validator,
    UserApiProcessor $user_api_processor,
    UserApiLoader $user_api_loader,
    UserApiResponseBuilder $user_api_response_builder
  ) {
    parent::__construct($translator, $authentication_manager);

    $this->validator = $user_api_validator;
    $this->processor = $user_api_processor;
    $this->loader = $user_api_loader;
    $this->response_builder = $user_api_response_builder;
  }

  /**
   * {@inheritdoc}
   *
   * @throws Exception
   */
  public function userPost(RegisterRequest $register_request, string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->setDefaultAcceptLanguageOnNull($accept_language);

    $error_response = $this->validator->validateRegistration($register_request, $accept_language);

    if ($this->validator->isRegistrationRequestInvalid($error_response)) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $this->addResponseHashToHeaders($responseHeaders, $error_response);

      return $error_response;
    }

    if ($register_request->isDryRun()) {
      $responseCode = Response::HTTP_NO_CONTENT;

      return null;
    }

    $user = $this->processor->registerUser($register_request);

    $responseCode = Response::HTTP_CREATED;
    $token = $this->authentication_manager->createAuthenticationTokenFromUser($user);
    $response = $this->response_builder->buildUserRegisteredResponse($token);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function userDelete(&$responseCode, array &$responseHeaders)
  {
    $responseCode = Response::HTTP_NO_CONTENT;

    $this->processor->deleteUser($this->authentication_manager->getAuthenticatedUser());

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function userGet(&$responseCode, array &$responseHeaders): ExtendedUserDataResponse
  {
    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildExtendedUserDataResponse($this->authentication_manager->getAuthenticatedUser());
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function userIdGet(string $id, &$responseCode, array &$responseHeaders)
  {
    $user = $this->loader->findUserByID($id);

    if (null === $user) {
      $responseCode = Response::HTTP_NOT_FOUND;

      return null;
    }

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildBasicUserDataResponse($user);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function userPut(UpdateUserRequest $update_user_request, string $accept_language = null, &$responseCode = null, array &$responseHeaders = null)
  {
    $accept_language = $this->setDefaultAcceptLanguageOnNull($accept_language);

    $error_response = $this->validator->validateUpdateRequest($update_user_request, $accept_language);

    if ($this->validator->isUpdateRequestInvalid($error_response)) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
      $this->addResponseHashToHeaders($responseHeaders, $error_response);

      return $error_response;
    }

    $responseCode = Response::HTTP_NO_CONTENT;

    if (!$update_user_request->isDryRun()) {
      $this->processor->updateUser($this->authentication_manager->getAuthenticatedUser(), $update_user_request);
    }

    return null;
  }

  /**
   * {@inheritdoc}
   */
  public function usersSearchGet(string $query, ?int $limit = 20, ?int $offset = 0, &$responseCode = null, array &$responseHeaders = null): array
  {
    $limit = $this->setDefaultLimitOnNull($limit);
    $offset = $this->setDefaultOffsetOnNull($offset);

    $users = $this->loader->searchUsers($query, $limit, $offset);

    $responseCode = Response::HTTP_OK;
    $response = $this->response_builder->buildUsersDataResponse($users);
    $this->addResponseHashToHeaders($responseHeaders, $response);

    return $response;
  }
}
