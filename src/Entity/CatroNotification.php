<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Generic Notification.
 *
 * @ORM\Table
 */

/**
 * @ORM\Entity(repositoryClass="App\Repository\CatroNotificationRepository")
 * @ORM\Table
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="notification_type", type="string")
 * @ORM\DiscriminatorMap({
 *     "default": "CatroNotification",
 *     "anniversary": "AnniversaryNotification",
 *     "achievement": "AchievementNotification",
 *     "comment": "CommentNotification",
 *     "like": "LikeNotification",
 *     "follow": "FollowNotification",
 *     "follow_program": "NewProgramNotification",
 *     "broadcast_notification": "BroadcastNotification",
 *     "remix_notification": "RemixNotification"
 * })
 */
class CatroNotification
{
  /**
   * @var int
   *
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @var User The user to which this CatroNotification will be shown. If the user gets deleted, this CatroNotification
   *           gets deleted as well.
   *
   * @ORM\ManyToOne(targetEntity="\App\Entity\User", inversedBy="notifications")
   * @ORM\JoinColumn(
   *     name="user",
   *     referencedColumnName="id",
   *     nullable=false
   * )
   */
  private $user;

  /**
   * @ORM\Column(name="title", type="string")
   */
  private $title;

  /**
   * @ORM\Column(name="message", type="text")
   */
  private $message;
  /**
   * @ORM\Column(name="seen", type="boolean", options={"default": false})
   */
  private $seen = false;

  private $twig_template = 'Notifications/NotificationTypes/catro_notification.html.twig';

  /**
   * CatroNotification constructor.
   *
   * @param mixed $title
   * @param mixed $message
   */
  public function __construct(User $user, $title = '', $message = '')
  {
    $this->user = $user;
    $this->title = $title;
    $this->message = $message;
  }

  public function getId(): int
  {
    return $this->id;
  }

  public function setId(int $id)
  {
    $this->id = $id;
  }

  public function setTitle(string $title): CatroNotification
  {
    $this->title = $title;

    return $this;
  }

  public function getTitle(): string
  {
    return $this->title;
  }

  public function setSeen(bool $seen): CatroNotification
  {
    $this->seen = $seen;

    return $this;
  }

  public function getSeen(): bool
  {
    return $this->seen;
  }

  public function setMessage(string $message): CatroNotification
  {
    $this->message = $message;

    return $this;
  }

  public function getMessage(): string
  {
    return $this->message;
  }

  /**
   * Sets he user to which this CatroNotification will be shown.
   *
   * @param User $user the user to which this CatroNotification will be shown
   */
  public function setUser(User $user): CatroNotification
  {
    $this->user = $user;

    return $this;
  }

  /**
   * Returns the user to which this CatroNotification will be shown.
   */
  public function getUser(): User
  {
    return $this->user;
  }

  /**
   * @return mixed
   */
  public function getTwigTemplate()
  {
    return $this->twig_template;
  }

  /**
   * @param mixed $twig_template
   */
  public function setTwigTemplate($twig_template)
  {
    $this->twig_template = $twig_template;
  }
}
