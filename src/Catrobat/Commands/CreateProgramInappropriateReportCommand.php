<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\RemixManipulationProgramManager;
use App\Entity\Program;
use App\Entity\ProgramInappropriateReport;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateProgramInappropriateReportCommand extends Command
{
  private UserManager $user_manager;

  private RemixManipulationProgramManager $remix_manipulation_program_manager;

  private EntityManagerInterface $entity_manager;

  public function __construct(UserManager $user_manager,
                              RemixManipulationProgramManager $program_manager,
                              EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->remix_manipulation_program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:report')
      ->setDescription('Report a project')
      ->addArgument('user', InputArgument::REQUIRED, 'User who reports on program')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets reported')
      ->addArgument('note', InputArgument::REQUIRED, 'Report message')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $username = $input->getArgument('user');
    $program_name = $input->getArgument('program_name');
    $note = $input->getArgument('note');

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($username);
    $program = $this->remix_manipulation_program_manager->findOneByName($program_name);

    if (null === $user || null === $program)
    {
      return -1;
    }

    if ($program->getUser() === $user)
    {
      return -1;
    }

    try
    {
      $this->reportProgram($program, $user, $note);
    }
    catch (Exception $e)
    {
      return -1;
    }
    $output->writeln('Reporting '.$program->getName());

    return 0;
  }

  private function reportProgram(Program $program, User $user, string $note)
  {
    $report = new ProgramInappropriateReport();
    $report->setReportingUser($user);
    $program->setVisible(false);
    $report->setCategory('Inappropriate');
    $report->setNote($note);
    $report->setProgram($program);
    $this->entity_manager->persist($report);
    $this->entity_manager->flush();
  }
}
