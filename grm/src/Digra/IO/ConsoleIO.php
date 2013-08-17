<?php

namespace Digra\IO;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;

/**
 * The Input/Output helper.
 *
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class ConsoleIO implements IOInterface {
  protected $input;
  protected $output;
  protected $helperSet;
  protected $lastMessage;
  private $startTime;

  /**
   * Constructor
   *
   * @param InputInterface  $input     The input instance
   * @param OutputInterface $output    The output instance
   * @param HelperSet       $helperSet The helperSet instance
   */
  public function __construct(InputInterface $input, OutputInterface $output, HelperSet $helperSet) {
    $this->input = $input;
    $this->output = $output;
    $this->helperSet = $helperSet;
  }

  public function enableDebugging($startTime) {
    $this->startTime = $startTime;
  }

  /**
   * {@inheritDoc}
   */
  public function isInteractive() {
    return $this->input->isInteractive();
  }

  /**
   * {@inheritDoc}
   */
  public function isDecorated() {
    return $this->output->isDecorated();
  }

  /**
   * {@inheritDoc}
   */
  public function isVerbose() {
    return $this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
  }

  /**
   * {@inheritDoc}
   */
  public function isVeryVerbose() {
    return $this->output->getVerbosity() >= 3; // OutputInterface::VERSOBITY_VERY_VERBOSE
  }

  /**
   * {@inheritDoc}
   */
  public function isDebug() {
    return $this->output->getVerbosity() >= 4; // OutputInterface::VERBOSITY_DEBUG
  }

  /**
   * {@inheritDoc}
   */
  public function write($messages, $newline = true) {
    if(null !== $this->startTime) {
      $messages = (array) $messages;
      $messages[0] = sprintf(
        '[%.1fMB/%.2fs] %s',
        memory_get_usage() / 1024 / 1024,
        microtime(true) - $this->startTime,
        $messages[0]
      );
    }
    $this->output->write($messages, $newline);
    $this->lastMessage = join($newline ? "\n" : '', (array) $messages);
  }

  /**
   * {@inheritDoc}
   */
  public function overwrite($messages, $newline = true, $size = null) {
    // messages can be an array, let's convert it to string anyway
    $messages = join($newline ? "\n" : '', (array) $messages);

    // since overwrite is supposed to overwrite last message...
    if(!isset($size)) {
      // removing possible formatting of lastMessage with strip_tags
      $size = strlen(strip_tags($this->lastMessage));
    }
    // ...let's fill its length with backspaces
    $this->write(str_repeat("\x08", $size), false);

    // write the new message
    $this->write($messages, false);

    $fill = $size - strlen(strip_tags($messages));
    if($fill > 0) {
      // whitespace whatever has left
      $this->write(str_repeat(' ', $fill), false);
      // move the cursor back
      $this->write(str_repeat("\x08", $fill), false);
    }

    if($newline) {
      $this->write('');
    }
    $this->lastMessage = $messages;
  }

  /**
   * {@inheritDoc}
   */
  public function ask($question, $default = null) {
    return $this->helperSet->get('dialog')->ask($this->output, $question, $default);
  }

  /**
   * {@inheritDoc}
   */
  public function askAndValidate($question, $validator, $attempts = false, $default = null) {
    return $this->helperSet->get('dialog')->askAndValidate($this->output, $question, $validator, $attempts, $default);
  }

  /**
   * {@inheritDoc}
   */
  public function askConfirmation($question, $default = true) {
    return $this->helperSet->get('dialog')->askConfirmation($this->output, $question, $default);
  }

  /**
   * {@inheritDoc}
   */
  public function askAndHideAnswer($question) {
    // handle windows
    if(defined('PHP_WINDOWS_VERSION_BUILD')) {
      $exe = __DIR__.'\\hiddeninput.exe';

      // handle code running from a phar
      if('phar:' === substr(__FILE__, 0, 5)) {
        $tmpExe = sys_get_temp_dir().'/hiddeninput.exe';

        // use stream_copy_to_stream instead of copy
        // to work around https://bugs.php.net/bug.php?id=64634
        $source = fopen(__DIR__.'\\hiddeninput.exe', 'r');
        $target = fopen($tmpExe, 'w+');
        stream_copy_to_stream($source, $target);
        fclose($source);
        fclose($target);
        unset($source, $target);

        $exe = $tmpExe;
      }

      $this->write($question, false);
      $value = rtrim(shell_exec($exe));
      $this->write('');

      // Clean up
      if(isset($tmpExe)) {
        unlink($tmpExe);
      }

      return $value;
    }

    if(file_exists('/usr/bin/env')) {
      // handle other OSs with bash/zsh/ksh/csh if available to hide the answer
      $test = "/usr/bin/env %s -c 'echo OK' 2> /dev/null";
      foreach(array('bash', 'zsh', 'ksh', 'csh') as $sh) {
        if('OK' === rtrim(shell_exec(sprintf($test, $sh)))) {
          $shell = $sh;
          break;
        }
      }
      if(isset($shell)) {
        $this->write($question, false);
        $readCmd = ($shell === 'csh') ? 'set mypassword = $<' : 'read -r mypassword';
        $command = sprintf("/usr/bin/env %s -c 'stty -echo; %s; stty echo; echo \$mypassword'", $shell, $readCmd);
        $value = rtrim(shell_exec($command));
        $this->write('');

        return $value;
      }
    }

    // not able to hide the answer, proceed with normal question handling
    return $this->ask($question);
  }

  /**
   * Asks the user to select one or more values.
   *
   * @param string|array    $question     The question to ask
   * @param array           $choices      List of choices to pick from
   * @param mixed           $default      The default answer if the user enters nothing
   * @param bool|integer    $attempts     Max number of times to ask before giving up (false by default, which means infinite)
   * @param string          $errorMessage Message which will be shown if invalid value from choice list would be picked
   *
   * @return integer|string The selected value (the key of the choices array)
   *
   * @throws \InvalidArgumentException
   */
  public function select($question, $choices, $default = null, $attempts = false, $errorMessage = 'Value "%s" is invalid') {
    $width = max(array_map('strlen', array_keys($choices)));
    $messages = (array) $question;
    foreach($choices as $key => $value) {
      $messages[] = sprintf("  [<info>%-${width}s</info>] %s", $key, $value);
    }
    $this->output->writeln($messages);

    $result = $this->askAndValidate('> ', function($picks) use ($choices, $errorMessage) {
      $picks = preg_split('/[, ]+/', $picks);
      foreach($picks as $pick) {
        if(empty($choices[$pick])) {
          throw new \InvalidArgumentException(sprintf($errorMessage, $pick));
        }
      }

      return $picks;
    }, $attempts, $default);

    return $result;
  }
}
