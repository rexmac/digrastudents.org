<?php

namespace Digra\Command\GamesResearchMap;

#use Digra\IO\IOInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class HtmlToCsvCommand extends \Digra\Command\Command {
  protected function configure() {
    $this->setName('grm:html2csv')
      ->setDescription('Parse HTML, clean/filter the data, and write to CSV.')
      ->setDefinition(array(
        new InputArgument('html', InputArgument::OPTIONAL, 'Path to HTML file to be parsed. Defaults to grm.raw.html'),
        new InputArgument('csv', InputArgument::OPTIONAL, 'Path to CSV file to write output to. File will be overwritten. Defaults to grm.csv'),
      ))
      ->setHelp(<<<EOT
The <info>html2csv</info> command parses the raw HTML Games Research Map, filters the data (i.e., cleans it up), and writes the output to a CSV file.
EOT
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $digra = $this->getDigra();
    $io = $this->getIO();

    try {
      $path = $input->getArgument('html');
      if(null === $path) {
        $path = 'grm.raw.html';
      }

      $html = file_get_contents($path);
      if($html === false || empty($html)) {
        throw new \RuntimeException('Failed to read HTML file or file is empty.');
      }

      $filename = $digra->parseResearchPositionsHtmlToCsv($html, $input->getArgument('csv'));

      $io->write('CSV written to ' . $filename);
      $io->write('Done.');

      return 0;
    } catch(\Exception $e) {
      $io->write(sprintf('<error>%s</error>', $e->getMessage()));
      return 1;
    }
  }
}
