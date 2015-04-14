<?php

namespace Account;

use \Monolog\Logger;

/**
 * Represents some type of third-party security exchange account,
 * for example, on Havelock Investments.
 *
 * Along with the {@link #fetchBalances()} of an {@link AccountType},
 * this interface adds {@link #fetchSecurities()} for an account instance,
 * that returns the securities "owned" by a particular account.
 */
interface SecurityExchangeAccountType extends AccountType {

  /**
   * Get all securities balances for this account.
   * May block.
   *
   * Returned results may include:
   * - owned: raw owned number of shares
   * - available: available number of shares to trade
   * - reserved: unavailable number of shares to trade
   *
   * @param $account fields that satisfy {@link #getFields()}
   * @return an array of ('security' => ('owned', 'available', 'reserved', ...))
   */
  public function fetchSecurities($account, Logger $logger);

  /**
   * Helper function to get all securities value for the given security for this account,
   * or {@code null} if there is no balance for this security.
   * May block.
   *
   * @param $account fields that satisfy {@link #getFields()}
   * @return array('owned', 'available', 'reserved', ...) or {@code null}
   */
  public function fetchSecurity($security, $account, Logger $logger);

  /**
   * Helper function to get the current, owned number of shares (units) of the given security
   * for this account.
   * May block.
   *
   * @param $account fields that satisfy {@link #getFields()} or {@code null}
   */
  public function fetchSecurityBalance($security, $account, Logger $logger);

}
