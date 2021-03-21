<?php

namespace App\Api;

use App\Api\Services\Authentication\OAuthApiProcessor;
use App\Api\Services\Authentication\OAuthApiValidator;
use App\Api\Services\Base\AbstractApiController;
use App\Api\Services\Base\AuthenticationManager;
use App\Api\Services\Base\PandaAuthenticationTrait;
use OpenAPI\Server\Api\AuthenticationApiInterface;
use OpenAPI\Server\Model\JWTResponse;
use OpenAPI\Server\Model\LoginRequest;
use OpenAPI\Server\Model\OAuthLoginRequest;
use OpenAPI\Server\Model\RefreshRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthenticationApi extends AbstractApiController implements AuthenticationApiInterface
{
  use PandaAuthenticationTrait;

  protected AuthenticationManager $authentication_manager;

  private OAuthApiValidator $o_auth_api_validator;
  private OAuthApiProcessor $o_auth_api_processor;

  public function __construct(
    TranslatorInterface $translator,
    AuthenticationManager $authentication_manager,
    OAuthApiValidator $o_auth_api_validator,
    OAuthApiProcessor $o_auth_api_processor
) {
    parent::__construct($translator, $authentication_manager);

    $this->o_auth_api_validator = $o_auth_api_validator;
    $this->o_auth_api_processor = $o_auth_api_processor;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationGet(&$responseCode, array &$responseHeaders)
  {
    // Check Token is handled by LexikJWTAuthenticationBundle
    // Successful requests are passed to this method.
    $responseCode = Response::HTTP_OK;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationPost(LoginRequest $login_request, &$responseCode, array &$responseHeaders)
  {
    // Login Process & token creation is handled by LexikJWTAuthenticationBundle
    // Successful requests are NOT passed to this method. This method will never be called.
    // The AuthenticationController:authenticatePostAction will only be used when Request was invalid.
    $responseCode = Response::HTTP_OK;

    return new JWTResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationDelete(string $x_refresh, &$responseCode, array &$responseHeaders)
  {
    // TODO: Implement authenticationDelete() method.

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationRefreshPost(RefreshRequest $refresh_request, &$responseCode, array &$responseHeaders)
  {
    //TODO: Implement authenticationRefreshPost() method

    $responseCode = Response::HTTP_NOT_IMPLEMENTED;

    return new JWTResponse();
  }

  /**
   * {@inheritdoc}
   */
  public function authenticationOauthPost(OAuthLoginRequest $o_auth_login_request, &$responseCode, array &$responseHeaders)
  {
    $resource_owner = $o_auth_login_request->getResourceOwner() ?? '';
    $id_token = $o_auth_login_request->getIdToken() ?? '';

    $resource_owner_method = 'validate'.ucfirst($resource_owner).'IdToken';

    if (!method_exists($this->o_auth_api_validator, $resource_owner_method)) {
      $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;

      return new JWTResponse();
    }

    $validation_response = $this->o_auth_api_validator->{$resource_owner_method}($id_token);

    if (!$validation_response) {
      $responseCode = Response::HTTP_UNAUTHORIZED;
      $token = new JWTResponse();

      return ['response_code' => $responseCode, 'token' => $token];
    }

    $response = $this->o_auth_api_processor->connectUserToAccount($id_token, $resource_owner);

    $responseCode = $response['response_code'];

    return $response['token'];
  }
}
