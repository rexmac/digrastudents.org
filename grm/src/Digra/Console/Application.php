<?php
namespace Digra\Console;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Digra\Command;
use Digra\Command\Helper\DialogHelper;
use Digra\Digra;
use Digra\Factory;
use Digra\IO\IOInterface;
use Digra\IO\ConsoleIO;
use Digra\Util\ErrorHandler;

/**
 * The console application that handles the commands
 *
 * Based on Composer CLI (http://getcomposer.org/)
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class Application extends BaseApplication {
  /**
   * @var Digra
   */
  protected $digra;

  /**
   * @var IOInterface
   */
  protected $io;

  /**
   * Console application logo
   *
   * @var string
   */
  private static $logo = '   ___  _ ________  ___
  / _ \(_) ___/ _ \/ _ |
 / // / / (_ / , _/ __ |
/____/_/\___/_/|_/_/ |_|
Student Reps Management Tool
';

  public function __construct() {
    if(function_exists('ini_set')) {
      ini_set('xdebug.show_exception_trace', false);
      ini_set('xdebug.scream', false);
    }
    if(function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
      date_default_timezone_set(@date_default_timezone_get());
    }

    ErrorHandler::register();
    parent::__construct('Digra', Digra::VERSION);
  }

  /**
   * {@inheritDoc}
   */
  public function run(InputInterface $input = null, OutputInterface $output = null) {
    if(null === $output) {
      $styles = array(
        'caution' => new OutputFormatterStyle('yellow'),
        'highlight' => new OutputFormatterStyle('yellow', 'black', array('bold')),
        'warning' => new OutputFormatterStyle('black', 'yellow')
      );
      $formatter = new OutputFormatter(null, $styles);
      $output = new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL, null, $formatter);
    }

    return parent::run($input, $output);
  }

  /**
   * {@inheritDoc}
   */
  public function doRun(InputInterface $input, OutputInterface $output) {
    $this->io = new ConsoleIO($input, $output, $this->getHelperSet());

    if(version_compare(PHP_VERSION, '5.3.2', '<')) {
      $output->writeln('<warning>The DiGRA-SRMT only officially supports PHP 5.3.2 and above, you will most likely encounter problems with your PHP '.PHP_VERSION.', upgrading is strongly recommended.</warning>');
    }

    if($input->hasParameterOption('--profile')) {
      $startTime = microtime(true);
      $this->io->enableDebugging($startTime);
    }

    if ($newWorkDir = $this->getNewWorkingDir($input)) {
      $oldWorkingDir = getcwd();
      chdir($newWorkDir);
    }

    $result = parent::doRun($input, $output);

    if(isset($oldWorkingDir)) {
      chdir($oldWorkingDir);
    }

    if(isset($startTime)) {
      $output->writeln('<info>Memory usage: '.round(memory_get_usage() / 1024 / 1024, 2).'MB (peak: '.round(memory_get_peak_usage() / 1024 / 1024, 2).'MB), time: '.round(microtime(true) - $startTime, 2).'s');
    }

    return $result;
  }

  /**
   * @param  InputInterface    $input
   * @throws \RuntimeException
   */
  private function getNewWorkingDir(InputInterface $input) {
    $workingDir = $input->getParameterOption(array('--working-dir', '-d'));
    if(false !== $workingDir && !is_dir($workingDir)) {
      throw new \RuntimeException('Invalid working directory specified.');
    }

    return $workingDir;
  }

  /**
   * @return Digra
   */
  public function getDigra($required = true) {
    if(null === $this->digra) {
      try {
        $this->digra = Factory::create($this->io);
      } catch(\InvalidArgumentException $e) {
        if($required) {
          $this->io->write($e->getMessage());
          exit(1);
        }
      }
    }

    return $this->digra;
  }

  /**
   * @return IOInterface
   */
  public function getIO() {
    return $this->io;
  }

  public function getHelp() {
    return self::$logo . parent::getHelp();
  }

  /**
   * Initializes all the Digra commands
   */
  protected function getDefaultCommands() {
    $commands = parent::getDefaultCommands();
    #$commands[] = new Command\AboutCommand();
    $commands[] = new Command\GamesResearchMap\FetchCommand();
    $commands[] = new Command\GamesResearchMap\HtmlToCsvCommand();
    $commands[] = new Command\GamesResearchMap\CsvToJsonCommand();
    #$commands[] = new Command\DependsCommand();
    #$commands[] = new Command\InitCommand();
    #$commands[] = new Command\InstallCommand();
    #$commands[] = new Command\CreateProjectCommand();
    #$commands[] = new Command\UpdateCommand();
    #$commands[] = new Command\SearchCommand();
    #$commands[] = new Command\ValidateCommand();
    #$commands[] = new Command\ShowCommand();
    #$commands[] = new Command\RequireCommand();
    #$commands[] = new Command\DumpAutoloadCommand();
    #$commands[] = new Command\StatusCommand();
    #$commands[] = new Command\ArchiveCommand();
    #$commands[] = new Command\DiagnoseCommand();
    #$commands[] = new Command\RunScriptCommand();

    #if('phar:' === substr(__FILE__, 0, 5)) {
    #  $commands[] = new Command\SelfUpdateCommand();
    #}

    return $commands;
  }

  /**
   * {@inheritDoc}
   */
  protected function getDefaultInputDefinition() {
    $definition = parent::getDefaultInputDefinition();
    $definition->addOption(new InputOption('--profile', null, InputOption::VALUE_NONE, 'Display timing and memory usage information'));
    $definition->addOption(new InputOption('--working-dir', '-d', InputOption::VALUE_REQUIRED, 'If specified, use the given directory as working directory.'));

    return $definition;
  }

  /**
   * {@inheritDoc}
   */
  protected function getDefaultHelperSet() {
    $helperSet = parent::getDefaultHelperSet();

    $helperSet->set(new DialogHelper());

    return $helperSet;
  }
}
