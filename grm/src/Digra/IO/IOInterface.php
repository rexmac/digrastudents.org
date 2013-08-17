<?php

namespace Digra\IO;

/**
 * The Input/Output helper interface.
 *
 * @author François Pluchino <francois.pluchino@opendisplay.com>
 */
interface IOInterface {
  /**
   * Is this input means interactive?
   *
   * @return bool
   */
  public function isInteractive();

  /**
   * Is this output verbose?
   *
   * @return bool
   */
  public function isVerbose();

  /**
   * Is the output very verbose?
   *
   * @return bool
   */
  public function isVeryVerbose();

  /**
   * Is the output in debug verbosity?
   *
   * @return bool
   */
  public function isDebug();

  /**
   * Is this output decorated?
   *
   * @return bool
   */
  public function isDecorated();

  /**
   * Writes a message to the output.
   *
   * @param string|array $messages The message as an array of lines or a single string
   * @param bool         $newline  Whether to add a newline or not
   */
  public function write($messages, $newline = true);

  /**
   * Overwrites a previous message to the output.
   *
   * @param string|array $messages The message as an array of lines or a single string
   * @param bool         $newline  Whether to add a newline or not
   * @param integer      $size     The size of line
   */
  public function overwrite($messages, $newline = true, $size = 80);

  /**
   * Asks a question to the user.
   *
   * @param string|array $question The question to ask
   * @param string       $default  The default answer if none is given by the user
   *
   * @return string The user answer
   *
   * @throws \RuntimeException If there is no data to read in the input stream
   */
  public function ask($question, $default = null);

  /**
   * Asks a confirmation to the user.
   *
   * The question will be asked until the user answers by nothing, yes, or no.
   *
   * @param string|array $question The question to ask
   * @param bool         $default  The default answer if the user enters nothing
   *
   * @return bool true if the user has confirmed, false otherwise
   */
  public function askConfirmation($question, $default = true);

  /**
   * Asks for a value and validates the response.
   *
   * The validator receives the data to validate. It must return the
   * validated data when the data is valid and throw an exception
   * otherwise.
   *
   * @param string|array $question  The question to ask
   * @param callback     $validator A PHP callback
   * @param integer      $attempts  Max number of times to ask before giving up (false by default, which means infinite)
   * @param string       $default   The default answer if none is given by the user
   *
   * @return mixed
   *
   * @throws \Exception When any of the validators return an error
   */
  public function askAndValidate($question, $validator, $attempts = false, $default = null);

  /**
   * Asks a question to the user and hide the answer.
   *
   * @param string $question The question to ask
   *
   * @return string The answer
   */
  public function askAndHideAnswer($question);
}
