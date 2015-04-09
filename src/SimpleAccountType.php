<?php

namespace Account;

use \Monolog\Logger;
use \Openclerk\Config;
use \Openclerk\Currencies\CurrencyFactory;

/**
 * Implements some basic helper methods for an account type.
 */
abstract class SimpleAccountType implements AccountType, AccountTypeInformation {

  /**
   * Helper function to get all balances for the given currency for this account,
   * or {@code null} if there is no balance for this currency.
   * May block.
   *
   * @param $account fields that satisfy {@link #getFields()}
   * @return array('confirmed', 'unconfirmed', 'hashrate', ...) or {@code null}
   */
  public function fetchBalance($currency, $account, CurrencyFactory $factory, Logger $logger) {
    $balances = $this->fetchBalances($account, $factory, $logger);
    if (isset($balances[$currency])) {
      return $balances[$currency];
    }

    return null;
  }

  /**
   * Helper function to get the current, confirmed, available balance of the given currency
   * for this account.
   * May block.
   *
   * @param $account fields that satisfy {@link #getFields()}
   * @return confirmed balance or {@code null}
   */
  public function fetchConfirmedBalance($currency, $account, CurrencyFactory $factory, Logger $logger) {
    $balance = $this->fetchBalance($currency, $account, $factory, $logger);
    if ($balance !== null) {
      return $balance['confirmed'];
    }
    return null;
  }

  /**
   * Basic implementation of {@link #checkFields()} using regular expressions.
   *
   * @return array of (error string, array(args)) errors or {@code false} if there are no errors
   */
  public function checkFields($account) {
    $errors = array();

    foreach ($this->getFields() as $key => $field) {
      $title = $field['title'];

      if (!isset($account[$key])) {
        if (!isset($errors[$key])) {
          $errors[$key] = array();
        }
        $errors[$key][] = array(
          "':title' needs to be provided." /* i18n */,
          array(":title" => $title),
        );
        continue;
      }

      if (isset($field['regexp'])) {
        if (!preg_match($field['regexp'], $account[$key])) {
          if (!isset($errors[$key])) {
            $errors[$key] = array();
          }
          $errors[$key][] = array(
            "Invalid value for ':title'." /* i18n */,
            array(":title" => $title),
          );
        }
      }

      if (isset($field['type'])) {
        if ($field['type'] == "confirm" && !$account[$key]) {
          if (!isset($errors[$key])) {
            $errors[$key] = array();
          }
          $errors[$key][] = array(
            "Need to confirm ':title'." /* i18n */,
            array(":title" => $title),
          );
        }
      }
    }

    return $errors;
  }

  static $throttled = array();

  /**
   * This allows all exchanges to optionally throttle multiple repeated
   * requests based on a runtime configuration value.
   * The throttle time is selected from either the
   * `accounts_NAME_throttle` or `accounts_throttle` config values,
   * or {@code $default} seconds;
   * which is the time in seconds to wait between repeated requests.
   * @param $default the default delay, or 3 seconds if not specified
   */
  public function throttle(Logger $logger, $default = 3) {
    if (isset(self::$throttled[$this->getCode()])) {
      $seconds = Config::get("accounts_" . $this->getCode() . "_throttle", Config::get("accounts_throttle", $default /* default */));
      $logger->info("Throttling for " . $seconds . " seconds");
      set_time_limit(30 + ($seconds * 2));
      sleep($seconds);
    }
    self::$throttled[$this->getCode()] = time();
  }

}
