<?php

namespace App\Admin\DB_Updater\Controller;

use App\Commands\Helpers\CommandHelper;
use App\Repository\CronJobRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CronJobsAdminController extends CRUDController
{
  protected CronJobRepository $cron_job_repository;
  protected EntityManagerInterface $entity_manager;

  public function __construct(CronJobRepository $cron_job_repository, EntityManagerInterface $entity_manager)
  {
    $this->cron_job_repository = $cron_job_repository;
    $this->entity_manager = $entity_manager;
  }

  public function listAction(Request $request = null): Response
  {
    return $this->renderWithExtraParams('Admin/DB_Updater/admin_cron_jobs.html.twig', [
      'action' => 'reset_cron_job',
      'triggerCronJobsUrl' => $this->admin->generateUrl('trigger_cron_jobs'),
    ]);
  }

  /**
   * @throws Exception
   */
  public function resetCronJobAction(Request $request): RedirectResponse
  {
    if (!$this->admin->isGranted('RESET_CRON_JOB')) {
      throw new AccessDeniedException();
    }

    $cron_job = $this->cron_job_repository->findByName($request->query->get('id'));
    if (is_null($cron_job)) {
      $this->addFlash('sonata_flash_error', 'Resetting cron job failed');
    }

    $this->entity_manager->remove($cron_job);
    $this->entity_manager->flush();
    $this->addFlash('sonata_flash_success', 'Resetting cron job successful. Job will be executed and added back to the list on the next run.');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws Exception
   */
  public function triggerCronJobsAction(KernelInterface $kernel): RedirectResponse
  {
    if (!$this->admin->isGranted('TRIGGER_CRON_JOB')) {
      throw new AccessDeniedException();
    }

    $output = new BufferedOutput();
    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:cronjob'], ['timeout' => 86400], '', $output, $kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Cron jobs finished successfully');
    } else {
      $this->addFlash('sonata_flash_error', "Running cron jobs failed!\n".$output->fetch());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
