<?php

namespace Account\Tests;

use Monolog\Logger;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\NullHandler;
use Monolog\Handler\ErrorLogHandler;
use Account\AccountType;
use Account\AccountFetchException;
use Account\SimpleAccountType;
use Openclerk\Config;
use Openclerk\Currencies\Currency;
use Openclerk\Currencies\CurrencyFactory;

/**
 * Implements a basic {@link SimpleAccountType}
 */
class TestSimpleAccountType extends SimpleAccountType {

  public function getName() {
    return "Test";
  }

  public function getCode() {
    return "test";
  }

  public function getURL() {
    return "";
  }

  public function getFields() {
    return array(
      'api_key' => array(
        'title' => "API Key",
        'regexp' => '#^[a-z]{3}$#',
      ),
      'api_secret' => array(
        'title' => "API Secret",
        'regexp' => "#^[0-9]{3}$#",
      ),
      'confirm' => array(
        'title' => "Confirm",
        'type' => 'confirm',
      ),
    );
  }

  public function fetchSupportedCurrencies(CurrencyFactory $factory, Logger $logger) {
    return array('btc');
  }

  /**
   * @return all account balances
   * @throws AccountFetchException if something bad happened
   */
  public function fetchBalances($account, CurrencyFactory $factory, Logger $logger) {
    if ($account['api_secret'] == '123') {
      return array('btc' => array('confirmed' => 123));
    }

    throw new AccountFetchException("Invalid account to test");
  }

}
