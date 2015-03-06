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

  /**
   * Get some field values for a valid account.
   * @return array of fields
   */
  abstract function getValidAccount();

  /**
   * Get some field values for a missing account,
   * but one that is still valid according to the fields.
   * @return array of fields
   */
  abstract function getMissingAccount();

  /**
   * Get some invalid field values.
   * @return array of fields
   */
  abstract function getInvalidAccount();

  function testValidFields() {
    $account = $this->getValidAccount();
    $errors = $this->account->checkFields($account);
    $this->assertEquals(0, count($errors), "Expected valid account to have valid fields: " . print_r($errors, true));
  }

  function testValidValues() {
    $account = $this->getValidAccount();
    $balances = $this->account->fetchBalances($account, $this->logger);
    $this->doTestValidValues($balances);
  }

  /**
   * Do tests as appropriate.
   */
  abstract function doTestValidValues($balances);

  function testMissingFields() {
    $account = $this->getMissingAccount();
    $errors = $this->account->checkFields($account);
    $this->assertEquals(0, count($errors), "Expected missing account to have valid fields: " . print_r($errors, true));
  }

  function testMissingValues() {
    $account = $this->getMissingAccount();
    try {
      $balances = $this->account->fetchBalances($account, $this->logger);
      $this->fail("Expected an AccountFetchException");
    } catch (AccountFetchException $e) {
      // expected
    }
  }

  function testInvalidFields() {
    $account = $this->getInvalidAccount();
    $this->assertGreaterThan(0, count($this->account->checkFields($account)), "Expected at least one error");
  }

  function testInvalidValues() {
    $account = $this->getInvalidAccount();
    try {
      $balances = $this->account->fetchBalances($account, $this->logger);
      $this->fail("Expected an AccountFetchException");
    } catch (AccountFetchException $e) {
      // expected
    }
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

  /**
   * We assert that there are no currencies in {@link #fetchBalances()} that are
   * not listed in {@link #fetchSupportedCurrencies()}.
   */
  function testValidAccountMatchCurrencies() {
    $supported = $this->account->fetchSupportedCurrencies($this->logger);
    $balances = $this->account->fetchBalances($this->getValidAccount(), $this->logger);

    foreach ($balances as $cur => $balance) {
      if (!in_array($cur, $supported)) {
        $this->fail("Did not expect currency '$cur' to be returned as a supported currency from fetchBalances()");
      }
    }
  }

}
