<?php
/* *************************************************************************
  Id: FaqMessage.php

  Provides user notifications to client and admin FAQ pages
  from the FAQ script.


  Tim Gall
  Copyright (c) 2009-2010 osfaq.oz-devworx.com.au - All Rights Reserved.
  http://osfaq.oz-devworx.com.au

  This file is part of osFaq.

  Released under the GNU General Public License v3 WITHOUT ANY WARRANTY.
  For licensing, see LICENSE.html or http://osfaq.oz-devworx.com.au/license

************************************************************************* */

class FaqMessage {

  /* added static class vars 2010-03-05 00:00, Tim Gall
   * NOTE: For backward compatibility we use "public static".
   * In actuality these should be "const" (php >= 5.3.0)
   * or "public static final" (php >= 6.0.0)
   */
  public static $error = 'error';
  public static $warning = 'warning';
  public static $success = 'success';
  public static $plain = 'plain';

  /**
   * FaqMessage::FaqMessage()
   */
  function __construct() {
    $this->messages = array();

    $this->session_to_stack();
  }


  /**
   * FaqMessage::add()
   *
   * @param mixed $message
   * @param string $type [optional] default = 'plain'
   */
  public function add($message, $type = 'error') {
    if ($type == FaqMessage::$error) {
      $this->messages[] = array('params' => 'class="messageHandlerError"', 'text' => FaqFuncs::format_image(DIR_WS_ICONS . 'error.gif', 'Error ') . '&nbsp;' . $message);
    } elseif ($type == FaqMessage::$warning) {
      $this->messages[] = array('params' => 'class="messageHandlerWarning"', 'text' => FaqFuncs::format_image(DIR_WS_ICONS . 'warning.gif', 'Warning ') . '&nbsp;' . $message);
    } elseif ($type == FaqMessage::$success) {
      $this->messages[] = array('params' => 'class="messageHandlerSuccess"', 'text' => FaqFuncs::format_image(DIR_WS_ICONS . 'success.gif', 'Success ') . '&nbsp;' . $message);
    } else {
      $this->messages[] = array('params' => 'class="messageHandlerPlain"', 'text' => $message);
    }
  }

  /**
   * FaqMessage::addNext()
   *
   * @param mixed $message
   * @param string $type [optional] default = 'plain'
   */
  public function addNext($message, $type = 'error') {
    if (!isset($_SESSION['osf_MessageStack'])) {
      $_SESSION['osf_MessageStack'] = array();
    }

    $_SESSION['osf_MessageStack'][] = array('text' => $message, 'type' => $type);
  }

  /**
   * Output any messages in the message stack.
   *
   * @return All messages on the stack neatly compiled into html ready for display.
   */
  public function output() {
    $output = '';

    foreach ($this->messages as $message) {
      $output .= '<div ' . $message['params'] . '>' . $message['text'] . '</div>' . "\n";
    }

    return $output;
  }

  /**
   * Get the count of all messages in this stack;
   * Messages held in the session var are not included
   *
   * @return int - how many messages on the stack
   */
  public function size() {
    return count($this->messages);
  }

  /**
   * Moves messages in the session array to the message stack
   */
  public function session_to_stack(){
    /// output any stored messages from addNext()
    if (isset($_SESSION['osf_MessageStack'])) {

      //print ('<pre><b>$_SESSION</b><br>');print_r($_SESSION);print ('</pre>');

      foreach ($_SESSION['osf_MessageStack'] as $message) {
        $this->add($message['text'], $message['type']);
      }
      unset($_SESSION['osf_MessageStack']);
    }
  }
}
?>