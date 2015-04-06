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
 * Abstracts away common test functionality for active accounts.
 */
abstract class AbstractActiveAccountTest extends AbstractAccountTest {

  /**
   * @override
   */
  function testActive() {
    $this->assertFalse($this->account instanceof \Account\DisabledAccount, "Should not be a disabled account");
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
    $balances = $this->account->fetchBalances($account, $this->factory, $this->logger);
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
      $balances = $this->account->fetchBalances($account, $this->factory, $this->logger);
      $this->fail("Expected an AccountFetchException");
    } catch (AccountFetchException $e) {
      // expected
      $this->assertGreaterThan(0, strlen($e->getMessage()), "Expected missing account to return an error message");
    }
  }

  function testInvalidFields() {
    $account = $this->getInvalidAccount();
    $this->assertGreaterThan(0, count($this->account->checkFields($account)), "Expected at least one error");
  }

  function testInvalidValues() {
    $account = $this->getInvalidAccount();
    try {
      $balances = $this->account->fetchBalances($account, $this->factory, $this->logger);
      $this->fail("Expected an AccountFetchException");
    } catch (AccountFetchException $e) {
      // expected
      $this->assertGreaterThan(0, strlen($e->getMessage()), "Expected an invalid account to return an error message");
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
    $supported = $this->account->fetchSupportedCurrencies($this->factory, $this->logger);
    $balances = $this->account->fetchBalances($this->getValidAccount(), $this->factory, $this->logger);

    foreach ($balances as $cur => $balance) {
      if (!in_array($cur, $supported)) {
        $this->fail("Did not expect currency '$cur' to be returned as a supported currency from fetchBalances()");
      }
    }
  }

  /**
   * Check that all currencies returned by {@link #fetchSupportedCurrencies()}
   * are three characters long, i.e. valid openclerk/currencies codes.
   */
  function testCurrencyCodes() {
    $supported = $this->account->fetchSupportedCurrencies($this->factory, $this->logger);

    foreach ($supported as $cur) {
      $this->assertEquals(3, strlen($cur), "Expected currency code length 3: got '$cur'");
      $this->assertRegexp("/^[a-z0-9]{3}$/", $cur);
    }
  }

}
