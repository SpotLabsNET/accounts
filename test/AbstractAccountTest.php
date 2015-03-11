<?php

namespace Account\Tests;

use Monolog\Logger;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\ErrorLogHandler;
use Account\AccountType;
use Account\AccountFetchException;
use Openclerk\Config;
use Openclerk\Currencies\Currency;

/**
 * Abstracts away common test functionality.
 */
abstract class AbstractAccountTest extends \PHPUnit_Framework_TestCase {

  function __construct(AccountType $account) {
    $this->logger = new Logger("test");
    $this->account = $account;

    if ($this->isDebug()) {
      $this->logger->pushHandler(new BufferHandler(new ErrorLogHandler()));
    } else {
      $this->logger->pushHandler(new NullHandler());
    }

    Config::merge(array(
      "get_contents_timeout" => 30,
    ));
  }

  function isDebug() {
    global $argv;
    if (isset($argv)) {
      foreach ($argv as $value) {
        if ($value === "--debug" || $value === "--verbose") {
          return true;
        }
      }
    }
    return false;
  }

  function testEmptyFields() {
    if (count($this->account->getFields()) > 0) {
      $errors = $this->account->checkFields(array() /* empty */);
      $this->assertNotTrue($errors, "Expected an account with zero fields to fail");
      $this->assertGreaterThan(0, count($errors), "Expected at least one error");
    }
  }

  function testCode() {
    $this->assertRegexp("/^[a-z0-9\_\-]{1,32}$/", $this->account->getCode());
  }

  function testAllFieldsHaveTitle() {
    foreach ($this->account->getFields() as $key => $field) {
      $this->assertTrue(isset($field['title']), "Field '$key' needs a title set");
    }
  }

  function testActive() {
    $this->assertTrue($this->account instanceof \Account\DisabledAccount, "Should be a disabled account");
  }

}
