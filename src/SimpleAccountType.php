<?php

namespace Account;

use \Monolog\Logger;

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
  public function fetchBalance($currency, $account, Logger $logger) {
    $balances = $this->fetchBalances($account, $logger);
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
  public function fetchConfirmedBalance($currency, $account, Logger $logger) {
    $balance = $this->fetchBalance($currency, $account, $logger);
    if ($balance !== null) {
      return $balance['confirmed'];
    }
    return null;
  }

  /**
   * Basic implementation of {@link #checkFields()} using regular expressions.
   * @return array of errors or {@code false} if there are no errors
   */
  public function checkFields($account) {
    $errors = array();

    foreach ($this->getFields() as $key => $field) {
      $title = $field['title'];

      if (!isset($account[$key])) {
        if (!isset($errors[$key])) {
          $errors[$key] = array();
        }
        $errors[$key][] = "'$title' needs to be provided.";
        continue;
      }

      if (isset($field['regexp'])) {
        if (!preg_match($field['regexp'], $account[$key])) {
          if (!isset($errors[$key])) {
            $errors[$key] = array();
          }
          $errors[$key][] = "Invalid value for '$title'.";
        }
      }
    }

    return $errors;
  }

  var $first_request = true;

  /**
   * This allows all exchanges to optionally throttle multiple repeated
   * requests based on a runtime configuration value.
   * The throttle time is selected from either the
   * `accounts_NAME_throttle` or `accounts_throttle` config values,
   * or three seconds;
   * which is the time in seconds to wait between repeated requests.
   */
  public function throttle(Logger $logger) {
    if (!$this->first_request) {
      $seconds = Config::get("accounts_" . $this->getCode() . "_throttle", Config::get("accounts_throttle", 3 /* default */));
      $logger->info("Throttling for " . $seconds . " seconds");
      set_time_limit(30 + ($seconds * 2));
      sleep($seconds);
    }
    $this->first_request = false;
  }

}
