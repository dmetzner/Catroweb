<?php

namespace App\Catrobat\Translate;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorAwareTrait
{
  private TranslatorInterface $translator;

  public function initTranslator(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  public function __(string $id, array $parameter = [], ?string $locale = null): string
  {
    return $this->translator->trans($id, $parameter, 'catroweb', $locale);
  }
}
