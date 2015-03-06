<?php

namespace Account;

use \Openclerk\Currencies\Currency;
use \Openclerk\Currencies\HashableCurrency;

/**
 * Represents some type of cryptocurrency miner or mining pool.
 */
interface Miner extends AccountType {

  /**
   * Get a list of all currencies that can return current hashrates.
   * This is not always strictly identical to all currencies that can be hashed;
   * for example, exchanges may trade in {@link HashableCurrency}s, but not actually
   * support mining.
   * May block.
   */
  public function fetchSupportedHashrateCurrencies($logger);

}
