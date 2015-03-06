<?php

namespace Account;

/**
 * Adds more user information about an account type.
 */
interface AccountTypeInformation {

  /**
   * @return the URL of the account type, or {@code null}
   */
  public function getURL();

}
