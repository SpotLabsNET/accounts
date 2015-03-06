<?php

namespace Account;

use \Monolog\Logger;

/**
 * Represents some type of third-party account, for example
 * a mining pool, exchange, wallet, miner.
 *
 * Instances of this account may be represented as an {@link Account},
 * and can be used to obtain current balances for this account.
 * For example, get the current wallet balances for a mining pool or exchange wallet.
 *
 * In theory this could also be extended to cryptocurrency addresses?
 */
interface AccountType {

  /**
   * @return the full name of the account type
   */
  public function getName();

  /**
   * @return a unique string representing this account type; must be lowercase and 1-32 characters
   */
  public function getCode();

  /**
   * @return an array of fields that need to be provided in order to get balances, e.g. API keys
   */
  public function getFields();

  /**
   * Get a list of all the currencies supported by this account (e.g. "btc", "ltc", ...).
   * Uses currency codes from openclerk/currencies.
   * May block.
   */
  public function fetchSupportedCurrencies(Logger $logger);

  /**
   * Get all balances for this account.
   * May block.
   *
   * @param $account fields that satisfy {@link #getFields()}
   * @return an array of ('cur' => ('confirmed', 'unconfirmed', 'hashrate', ...))
   */
  public function fetchBalances($account, Logger $logger);

  /**
   * Helper function to get all balances for the given currency for this account,
   * or {@code null} if there is no balance for this currency.
   * May block.
   *
   * @param $account fields that satisfy {@link #getFields()}
   * @return array('confirmed', 'unconfirmed', 'hashrate', ...) or {@code null}
   */
  public function fetchBalance($currency, $account, Logger $logger);

  /**
   * Helper function to get the current, confirmed, available balance of the given currency
   * for this account.
   * May block.
   *
   * @param $account fields that satisfy {@link #getFields()}
   */
  public function fetchConfirmedBalance($currency, $account, Logger $logger);

}
