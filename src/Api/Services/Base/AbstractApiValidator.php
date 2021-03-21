<?php

namespace App\Api\Services\Base;

use App\Catrobat\Translate\TranslatorAwareTrait;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractApiValidator
{
  use TranslatorAwareTrait;

  private ValidatorInterface $validator;

  private ?ValidationWrapper $validationWrapper = null;

  public function __construct(ValidatorInterface $validator, TranslatorInterface $translator)
  {
    $this->validator = $validator;
    $this->initTranslator($translator);
  }

  public function validate($value, $constraints = null, $groups = null): ConstraintViolationListInterface
  {
    return $this->validator->validate($value, $constraints, $groups);
  }

  public function getValidationWrapper(): ValidationWrapper
  {
    if (null === $this->validationWrapper) {
      $this->validationWrapper = new ValidationWrapper();
    }

    return $this->validationWrapper;
  }
}
