<?php

namespace Account;

use \Monolog\Logger;

/**
 * Represents an account that is no longer valid, but it might be
 * useful to list account information for historical reasons.
 */
interface DisabledAccount {

  /**
   * When was this account type disabled?
   * @return some parseable date string
   */
  public function disabledAt();

}
