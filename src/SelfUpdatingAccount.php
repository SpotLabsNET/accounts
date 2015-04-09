<?php

namespace Account;

/**
 * Represents an {@link AccountType} that requires some sort of self-updating
 * mechanism with the account data.
 *
 * This is necessary for e.g. OAuth2 updating access_tokens, refresh_tokens
 */
interface SelfUpdatingAccount {

  /**
   * When we have finished with this account, register this callback
   * that will update the account data with the given new data.
   *
   * This is necessary for e.g. OAuth2 updating access_tokens, refresh_tokens
   */
  function registerAccountUpdateCallback($callback);

}
