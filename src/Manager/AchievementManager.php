<?php

namespace App\Manager;

use App\Entity\Achievements\Achievement;
use App\Entity\Achievements\UserAchievement;
use App\Entity\Program;
use App\Entity\User;
use App\Repository\Achievements\AchievementRepository;
use App\Repository\Achievements\UserAchievementRepository;
use App\Utils\TimeUtils;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

class AchievementManager
{
  protected EntityManagerInterface $entity_manager;
  protected AchievementRepository $achievement_repository;
  protected UserAchievementRepository $user_achievement_repository;

  public function __construct(EntityManagerInterface $entity_manager,
                              AchievementRepository $achievement_repository,
                              UserAchievementRepository $user_achievement_repository)
  {
    $this->entity_manager = $entity_manager;
    $this->achievement_repository = $achievement_repository;
    $this->user_achievement_repository = $user_achievement_repository;
  }

  public function findAchievementByInternalTitle(string $internal_title): ?Achievement
  {
    return $this->achievement_repository->findAchievementByInternalTitle($internal_title);
  }

  /**
   * @return Achievement[]
   */
  public function findAllEnabledAchievements(): array
  {
    return $this->achievement_repository->findAllEnabledAchievements();
  }

  /**
   * @return Achievement[]
   */
  public function findAllAchievements(): array
  {
    return $this->achievement_repository->findAll();
  }

  public function countAllEnabledAchievements(): int
  {
    return $this->achievement_repository->countAllEnabledAchievements();
  }

  /**
   * @return UserAchievement[]
   */
  public function findAllUserAchievements(): array
  {
    return $this->user_achievement_repository->findAll();
  }

  public function findUserAchievementsOfAchievement(string $internal_title): array
  {
    $achievement = $this->findAchievementByInternalTitle($internal_title);

    return $this->user_achievement_repository->findBy([
      'achievement' => $achievement->getId(),
    ]);
  }

  public function countUserAchievementsOfAchievement(int $achievement_id): int
  {
    return $this->user_achievement_repository->count([
      'achievement' => $achievement_id,
    ]);
  }

  public function isAchievementAlreadyUnlocked(string $user_id, int $achievement_id): bool
  {
    return $this->user_achievement_repository->count([
      'user' => $user_id,
      'achievement' => $achievement_id,
    ]) > 0;
  }

  /**
   * @return UserAchievement[]
   */
  public function findUserAchievements(User $user): array
  {
    return $this->user_achievement_repository->findUserAchievements($user);
  }

  public function countUnseenUserAchievements(User $user): int
  {
    return $this->user_achievement_repository->countUnseenUserAchievements($user);
  }

  /**
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function readAllUnseenAchievements(User $user): void
  {
    $this->user_achievement_repository->readAllUnseenAchievements($user);
  }

  /**
   * @return Achievement[]
   */
  public function findUnlockedAchievements(User $user): array
  {
    return $this->mapUserAchievementsToAchievements($this->findUserAchievements($user));
  }

  /**
   * @return Achievement[]
   */
  public function findLockedAchievements(User $user): array
  {
    $achievements = $this->findAllEnabledAchievements();
    $unlocked_achievements = $this->findUnlockedAchievements($user);
    $achievements_unlocked_id_list = array_map(function (Achievement $achievement) {
      return $achievement->getId();
    }, $unlocked_achievements);

    return array_filter($achievements, function (Achievement $achievement) use ($achievements_unlocked_id_list) {
      return !in_array($achievement->getId(), $achievements_unlocked_id_list, true);
    });
  }

  public function findMostRecentUserAchievement(User $user): ?UserAchievement
  {
    return $this->user_achievement_repository->findMostRecentUserAchievement($user);
  }

  /**
   * @throws Exception
   */
  public function unlockAchievementVerifiedDeveloper(User $user): ?UserAchievement
  {
    return $this->unlockAchievement($user, Achievement::VERIFIED_DEVELOPER, $user->getCreatedAt());
  }

  /**
   * @throws Exception
   */
  public function unlockAchievementPerfectProfile(User $user): ?UserAchievement
  {
    if (is_null($user->getAvatar())) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::PERFECT_PROFILE);
  }

  /**
   * @throws Exception
   */
  public function unlockAchievementBronzeUser(User $user): ?UserAchievement
  {
    if (count($user->getFollowing()) <= 0) {
      return null;
    }

    if (count($user->getPrograms()) <= 0) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::BRONZE_USER);
  }

  /**
   * @throws Exception
   */
  public function unlockAchievementSilverUser(User $user): ?UserAchievement
  {
    if ($user->getCreatedAt() > new DateTime('-1 years')) {
      return null;
    }
    $years_with_project_uploads = [];
    foreach ($user->getPrograms() as $project) {
      /** @var Program $project */
      $year = $project->getUploadedAt()->format('Y');
      $years_with_project_uploads[$year] = true;
    }
    if (count($years_with_project_uploads) < 1) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::SILVER_USER);
  }

  /**
   * @throws Exception
   */
  public function unlockAchievementGoldUser(User $user): ?UserAchievement
  {
    if ($user->getCreatedAt() > new DateTime('-4 years')) {
      return null;
    }
    $years_with_project_uploads = [];
    foreach ($user->getPrograms() as $project) {
      /** @var Program $project */
      $year = $project->getUploadedAt()->format('Y');
      $years_with_project_uploads[$year] = true;
    }
    if (count($years_with_project_uploads) < 4) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::GOLD_USER);
  }

  /**
   * @throws Exception
   */
  public function unlockAchievementDiamondUser(User $user): ?UserAchievement
  {
    if ($user->getCreatedAt() > new DateTime('-7 years')) {
      return null;
    }

    $years_with_project_uploads = [];
    foreach ($user->getPrograms() as $project) {
      /** @var Program $project */
      $year = $project->getUploadedAt()->format('Y');
      $years_with_project_uploads[$year] = true;
    }
    if (count($years_with_project_uploads) < 7) {
      return null;
    }

    return $this->unlockAchievement($user, Achievement::DIAMOND_USER);
  }

  /**
   * @throws Exception
   */
  protected function unlockAchievement(User $user, string $internal_title, ?DateTime $unlocked_at = null): ?UserAchievement
  {
    $achievement = $this->findAchievementByInternalTitle($internal_title);
    if (is_null($achievement)) {
      return null;
    }

    if ($this->isAchievementAlreadyUnlocked($user->getId(), $achievement->getId())) {
      return null;
    }

    $user_achievement = new UserAchievement();
    $user_achievement->setUser($user);
    $user_achievement->setAchievement($achievement);
    $user_achievement->setUnlockedAt($unlocked_at ?? TimeUtils::getDateTime());

    $this->entity_manager->persist($user_achievement);
    $this->entity_manager->flush();

    return $user_achievement;
  }

  /**
   * @param UserAchievement[] $user_achievements
   *
   * @return Achievement[]
   */
  protected function mapUserAchievementsToAchievements(array $user_achievements): array
  {
    return array_map(function (UserAchievement $achievement) {
      return $achievement->getAchievement();
    }, $user_achievements);
  }
}
