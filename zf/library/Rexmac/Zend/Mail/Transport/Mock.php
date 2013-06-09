<?php
namespace Rexmac\Zend\Mail\Transport;

use Zend_Mail_Transport_Abstract,
    Zend_Mail_Transport_Exception;

/**
 * Mock Zend_Mail transport class for use in mocking/testing SMTP transport.
 *
 * @author Rex McConnell <rex@rexmac.com>
 */
class Mock extends Zend_Mail_Transport_Abstract {
  /**
   * Zend_Mail object
   *
   * @var Zend_Mail
   */
  public $mail = null;

  /**
   * Return path of email.
   *
   * @var string
   */
  public $returnPath = null;

  /**
   * Subject line of email.
   *
   * @var string
   */
  public $subject = null;

  /**
   * From line of mail
   *
   * @var string
   */
  public $from = null;

  /**
   * Array of message headers
   * @var array
   */
  public $headers = null;

  /**
   * Whether or not the _sendMail method ha been called.
   *
   * @var bool
   */
  public $called = false;

  /**
   * Force the _sendMail method to throw an exception
   *
   * @var bool
   */
  public $forceException = false;

  /**
   * Send an email independent from the used transport
   *
   * The requisite information for the email will be found in the following
   * properties:
   *
   * @return void
   */
  public function _sendMail() {
    if($this->forceException) { throw new Zend_Mail_Transport_Exception('Unable to send mail.'); }
    $this->mail       = $this->_mail;
    $this->subject    = $this->_mail->getSubject();
    $this->from       = $this->_mail->getFrom();
    $this->returnPath = $this->_mail->getReturnPath();
    $this->headers    = $this->_headers;
    $this->called     = true;
  }
}
