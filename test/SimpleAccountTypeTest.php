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
use Openclerk\Currencies\CurrencyFactory;

/**
 * Tests functionality within {@link SimpleAccountType}
 */
class SimpleAccountTypeTest extends AbstractActiveAccountTest {

  function __construct() {
    parent::__construct(new TestSimpleAccountType());
  }

  /**
   * Get some field values for a valid account.
   * @return array of fields
   */
  function getValidAccount() {
    return array(
      'api_key' => 'abc',
      'api_secret' => '123',
      'confirm' => true,
    );
  }

  /**
   * Get some field values for a missing account,
   * but one that is still valid according to the fields.
   * @return array of fields
   */
  function getMissingAccount() {
    return array(
      'api_key' => 'def',
      'api_secret' => '456',
      'confirm' => true,
    );
  }

  /**
   * Get some invalid field values.
   * @return array of fields
   */
  function getInvalidAccount() {
    return array(
      'api_key' => 'abc123',
      'api_secret' => 'hello',
      'confirm' => false,
    );
  }

  function doTestValidValues($balances) {
    $this->assertEquals(123, $balances['btc']['confirmed']);
  }

  function getAccountsJSON() {
    throw new \Exception("This should not be called");
  }

  function testUniqueCode() {
    // disable
  }

  function testCodeInAccountsJson() {
    // disable
  }

  function testInvalidApiKey() {
    $account = $this->getInvalidAccount();
    $errors = $this->account->checkFields($account);
    $this->assertTrue(isset($errors['api_key']), "There should be an error set for 'api_key'");
    $this->assertEquals(
      array(array("Invalid value for ':title'.", array(":title" => "API Key"))),
      $errors['api_key']);
  }

  function testMissingApiKey() {
    $account = $this->getInvalidAccount();
    unset($account['api_key']);
    $errors = $this->account->checkFields($account);
    $this->assertTrue(isset($errors['api_key']), "There should be an error set for 'api_key'");
    $this->assertEquals(
      array(array("':title' needs to be provided.", array(":title" => "API Key"))),
      $errors['api_key']);
  }

  function testInvalidConfirm() {
    $account = $this->getInvalidAccount();
    $errors = $this->account->checkFields($account);
    $this->assertTrue(isset($errors['confirm']), "There should be an error set for 'confirm'");
    $this->assertEquals(
      array(array("Need to confirm ':title'.", array(":title" => "Confirm"))),
      $errors['confirm']);
  }

  function testMissingConfirm() {
    $account = $this->getInvalidAccount();
    unset($account['confirm']);
    $errors = $this->account->checkFields($account);
    $this->assertTrue(isset($errors['confirm']), "There should be an error set for 'confirm'");
    $this->assertEquals(
      array(array("':title' needs to be provided.", array(":title" => "Confirm"))),
      $errors['confirm']);
  }

}
