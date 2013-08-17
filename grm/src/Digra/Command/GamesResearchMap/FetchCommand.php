<?php

namespace Digra\Command\GamesResearchMap;

#use Digra\IO\IOInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class FetchCommand extends \Digra\Command\Command {
  protected function configure() {
    $this->setName('grm:fetch')
      ->setDescription('Fetch research positions from old DiGRA website.')
      ->setDefinition(array(
        #new InputArgument('package', InputArgument::OPTIONAL, 'Package name to be installed'),
        #new InputArgument('directory', InputArgument::OPTIONAL, 'Directory where the files should be created'),
        #new InputArgument('version', InputArgument::OPTIONAL, 'Version, will defaults to latest'),
        #new InputOption('stability', 's', InputOption::VALUE_REQUIRED, 'Minimum-stability allowed (unless a version is specified).', 'stable'),
        #new InputOption('prefer-source', null, InputOption::VALUE_NONE, 'Forces installation from package sources when possible, including VCS information.'),
        #new InputOption('prefer-dist', null, InputOption::VALUE_NONE, 'Forces installation from package dist even for dev versions.'),
      ))
      ->setHelp(<<<EOT
The <info>fetch</info> command...
EOT
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $digra = $this->getDigra();
    $io = $this->getIO();

    try {
      $filename = $digra->fetchResearchPositions();

      $io->write('Raw HTML written to ' . $filename);
      $io->write('Done.');

      return 0;
    } catch(\Exception $e) {
      $io->write(sprintf('<error>%s</error>', $e->getMessage()));
      return 1;
    }
  }
}
