<?php

namespace App\Api\Services\Base;

class ValidationWrapper
{
  private array $errors = [];

  public function addError(string $error): ValidationWrapper
  {
    $errors[] = $error;

    return $this;
  }

  public function hasError(): bool
  {
    return count($this->errors) > 0;
  }

  public function getError(): string
  {
    return $this->hasError() ? '' : $this->errors[0];
  }
}
