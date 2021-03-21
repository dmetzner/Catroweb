<?php

namespace App\Api\Services\User;

use App\Api\Services\Base\AbstractApiValidator;
use App\Entity\User;
use App\Entity\UserManager;
use OpenAPI\Server\Model\RegisterErrorResponse;
use OpenAPI\Server\Model\RegisterRequest;
use OpenAPI\Server\Model\UpdateUserErrorResponse;
use OpenAPI\Server\Model\UpdateUserRequest;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserApiValidator.
 */
class UserApiValidator extends AbstractApiValidator
{
  const MIN_PASSWORD_LENGTH = 6;
  const MAX_PASSWORD_LENGTH = 4096;

  const MIN_USERNAME_LENGTH = 3;
  const MAX_USERNAME_LENGTH = 180;

  private UserManager $user_manager;

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator, UserManager $user_manager)
  {
    parent::__construct($validator, $translator);

    $this->user_manager = $user_manager;
  }

  public function isRegistrationRequestInvalid(RegisterErrorResponse $error_response): bool
  {
    return $error_response->getEmail()
      || $error_response->getUsername()
      || $error_response->getPassword();
  }

  public function validateRegistration(RegisterRequest $request, string $locale): RegisterErrorResponse
  {
    $response = new RegisterErrorResponse();

    $this->validateEmail($request->getEmail(), $locale, true);

    $this->validateUsername($request->getUsername(), $locale, true);

    $this->validatePassword($request->getPassword(), $locale, true);

    return $response;
  }

  public function validateUpdateRequest(UpdateUserRequest $request, string $locale): UpdateUserErrorResponse
  {
    $response = new UpdateUserErrorResponse();

    if (0 !== strlen($request->getCountry())) {
      $validate_country = $this->validateCountry($request->getCountry(), $locale);
      if (!empty($validate_country)) {
        $response->setCountry($validate_country);
      }
    }

    if (0 !== strlen($request->getEmail()) || !is_null($request->getEmail())) {
      $validate_email = $this->validateEmail($request->getEmail(), $locale);
      if (!empty($validate_email)) {
        $response->setEmail($validate_email);
      }
    }

    if (0 !== strlen($request->getUsername()) || !is_null($request->getUsername())) {
      $validate_username = $this->validateUsername($request->getUsername(), $locale);
      if (!empty($validate_username)) {
        $response->setUsername($validate_username);
      }
    }

    if (0 !== strlen($request->getPassword()) || !is_null($request->getPassword())) {
      $validate_password = $this->validatePassword($request->getPassword(), $locale);
      if (!empty($validate_password)) {
        $response->setPassword($validate_password);
      }
    }

    return $response;
  }

  public function isUpdateRequestInvalid(UpdateUserErrorResponse $error_response): bool
  {
    return $error_response->getUsername()
      || $error_response->getEmail()
      || $error_response->getPassword()
      || $error_response->getCountry();
  }

  private function validateEmail(string $email, string $locale, bool $missing = false): string
  {
    if (0 === strlen($email)) {
      return $this->__($missing ? 'api.registerUser.emailMissing' : 'api.registerUser.emailEmpty', [], $locale);
    }
    if (0 !== count($this->validate($email, new Email()))) {
      return $this->__('api.registerUser.emailInvalid', [], $locale);
    }
    if (null != $this->user_manager->findUserByEmail($email)) {
      return $this->__('api.registerUser.emailAlreadyInUse', [], $locale);
    }

    return '';
  }

  private function validateUsername(string $username, string $locale, bool $missing = false): string
  {
    if (0 === strlen($username)) {
      return $this->__($missing ? 'api.registerUser.usernameMissing' : 'api.registerUser.usernameEmpty', [], $locale);
    }
    if (strlen($username) < self::MIN_USERNAME_LENGTH) {
      return $this->__('api.registerUser.usernameTooShort', [], $locale);
    }
    if (strlen($username) > self::MAX_USERNAME_LENGTH) {
      return $this->__('api.registerUser.usernameTooLong', [], $locale);
    }
    if (filter_var(str_replace(' ', '', $username), FILTER_VALIDATE_EMAIL)) {
      return $this->__('api.registerUser.usernameContainsEmail', [], $locale);
    }
    if (null != $this->user_manager->findUserByUsername($username)) {
      return $this->__('api.registerUser.usernameAlreadyInUse', [], $locale);
    }
    if (0 === strncasecmp($username, User::$SCRATCH_PREFIX, strlen(User::$SCRATCH_PREFIX))) {
      return $this->__('api.registerUser.usernameInvalid', [], $locale);
    }

    return '';
  }

  private function validatePassword(string $password, string $locale, bool $missing = false): string
  {
    if (0 === strlen($password)) {
      return $this->__($missing ? 'api.registerUser.passwordMissing' : 'api.registerUser.passwordEmpty', [], $locale);
    }
    if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
      return $this->__('api.registerUser.passwordTooShort', [], $locale);
    }
    if (strlen($password) > self::MAX_PASSWORD_LENGTH) {
      return $this->__('api.registerUser.passwordTooLong', [], $locale);
    }
    if (!mb_detect_encoding($password, 'ASCII', true)) {
      return $this->__('api.registerUser.passwordInvalidChars', [], $locale);
    }

    return '';
  }

  private function validateCountry(string $country, string $locale): string
  {
    return Countries::exists($country) ? '' : $this->__('api.registerUser.countryCodeInvalid', [], $locale);
  }
}
