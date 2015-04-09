<?php

namespace Account;

use \Monolog\Logger;

/**
 * Represents an {@link AccountType} that allows fields to <i>instead</i> be
 * populated by doing something with the user.
 */
interface UserInteractionAccount {

  /**
   * Prepare the user agent to redirect, etc.
   * If user interaction is complete, instead returns an array of valid field values.
   *
   * @return either {@code null} (interaction is not yet complete) or an array of valid field values
   * @throws AccountFetchException if something bad happened
   */
  function interaction(Logger $logger);

}
