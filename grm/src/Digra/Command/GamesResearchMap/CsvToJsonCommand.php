<?php

namespace Digra\Command\GamesResearchMap;

#use Digra\IO\IOInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class CsvToJsonCommand extends \Digra\Command\Command {
  protected function configure() {
    $this->setName('grm:csv2json')
      ->setDescription('Convert CSV to JSON.')
      ->setDefinition(array(
        new InputArgument('csv', InputArgument::OPTIONAL, 'Path to CSV file to be parsed. Defaults to grm.csv'),
        new InputArgument('json', InputArgument::OPTIONAL, 'Path to JSON file to write output to. File will be overwritten. Defaults to grm.json'),
      ))
      ->setHelp(<<<EOT
The <info>html2csv</info> command converts the Games Research Map from CSV format to JSON format.
EOT
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $digra = $this->getDigra();
    $io = $this->getIO();

    try {
      $path = $input->getArgument('csv');
      if(null === $path) {
        $path = 'grm.csv';
      }

      $csv = file_get_contents($path);
      if($csv === false || empty($csv)) {
        throw new \RuntimeException('Failed to read CSV file or file is empty.');
      }

      $filename = $digra->csvToJson($csv, $input->getArgument('json'));

      $io->write('JSON written to ' . $filename);
      $io->write('Done.');

      return 0;
    } catch(\Exception $e) {
      $io->write(sprintf('<error>%s</error>', $e->getMessage()));
      return 1;
    }
  }
}
