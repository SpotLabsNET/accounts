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

}
