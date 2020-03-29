<?php

namespace App\Catrobat\Requests;

use App\Entity\GameJam;
use App\Entity\User;
use Symfony\Component\HttpFoundation\File\File;

class AddProgramRequest
{
  private User $user;

  private File $program_file;

  private string $ip;

  private ?GameJam $game_jam;

  private ?string $language;

  private string $flavor;

  public function __construct(User $user, File $program_file, $ip = '127.0.0.1', GameJam $game_jam = null,
                              string $language = null, $flavor = 'pocketcode')
  {
    $this->user = $user;
    $this->program_file = $program_file;
    $this->ip = $ip;
    $this->game_jam = $game_jam;
    $this->language = $language;
    $this->flavor = $flavor;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): void
  {
    $this->user = $user;
  }

  public function getProgramFile(): File
  {
    return $this->program_file;
  }

  public function setProgramFile(File $program_file)
  {
    $this->program_file = $program_file;
  }

  public function getIp(): string
  {
    return $this->ip;
  }

  public function getGameJam(): ?GameJam
  {
    return $this->game_jam;
  }

  public function getLanguage(): ?string
  {
    return $this->language;
  }

  public function setLanguage(?string $language)
  {
    $this->language = $language;
  }

  public function getFlavor(): string
  {
    return $this->flavor;
  }
}
