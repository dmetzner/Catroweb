<?php

namespace App\Api\Services\Projects;

use App\Api\Services\Base\AbstractApiValidator;
use App\Api\Services\Base\ValidationWrapper;
use App\Entity\UserManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsApiValidator extends AbstractApiValidator
{
  private UserManager $user_manager;

  public function __construct(
    ValidatorInterface $validator,
    TranslatorInterface $translator,
    UserManager $user_manager
  ) {
    parent::__construct($validator, $translator);

    $this->user_manager = $user_manager;
  }

  public function validateUserExists(string $user_id): bool
  {
    return '' !== trim($user_id)
      && !ctype_space($user_id)
      && null !== $this->user_manager->findOneBy(['id' => $user_id]);
  }

  public function validateUploadFile(string $checksum, UploadedFile $file, string $locale): ValidationWrapper
  {
    $validation_wrapper = $this->getValidationWrapper();

    if (!$file->isValid()) {
      return $validation_wrapper->addError($this->__('api.projectsPost.upload_error', [], $locale));
    }

    $calculated_checksum = md5_file($file->getPathname());
    if (strtolower($calculated_checksum) != strtolower($checksum)) {
      return $validation_wrapper->addError($this->__('api.projectsPost.invalid_checksum', [], $locale));
    }

    return $validation_wrapper;
  }
}
