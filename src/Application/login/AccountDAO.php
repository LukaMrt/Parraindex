<?php

declare(strict_types=1);

namespace App\Application\login;

use App\Entity\old\account\Account;
use App\Entity\old\account\Password;
use App\Entity\old\person\Identity;

/**
 * DAO to manage accounts
 */
interface AccountDAO
{
    /**
     * Retrieves the password of the account with the given login
     * @param string $login the login of the account
     * @return Password the password of the account, empty if the account does not exist
     */
    public function getAccountPassword(string $login): Password;


    /**
     * Creates a new account
     * @param Account $account The account to create
     */
    public function createAccount(Account $account): void;


    /**
     * Checks if the account with the given login exists
     * @param string $login the login of the account
     * @return bool true if the account exists, false otherwise
     */
    public function existsAccount(string $login): bool;


    /**
     * Checks if the account with the given identity exists
     * @param Identity $identity the identity of the account
     * @return bool true if the account exists, false otherwise
     */
    public function existsAccountByIdentity(Identity $identity): bool;


    /**
     * Creates a temporary account (used for signup before email confirmation)
     * @param Account $account the temporary account to create
     * @param string $token the token to confirm the account
     */
    public function createTemporaryAccount(Account $account, string $token): void;


    /**
     * Retrieves the temporary account with the given token
     * @param string $token the token of the temporary account
     * @return Account the temporary account, with id = -1 if the account does not exist
     */
    public function getTemporaryAccountByToken(string $token): Account;


    /**
     * Deletes a temporary account
     * @param Account $account the temporary account to delete
     */
    public function deleteTemporaryAccount(Account $account): void;


    /**
     * Retrieves the account with the given login
     * @param string $login the login of the account
     * @return Account|null the account, null if the account does not exist
     */
    public function getAccountByLogin(string $login): ?Account;


    /**
     * Creates a ResetPassword entry
     * @param Account $account the account which asks to reset its password
     * @param string $token the token to confirm the reset
     */
    public function createResetpassword(Account $account, string $token): void;


    /**
     * Retrieves the account related to a ResetPassword entry with the given token
     * @param string $token the token of the ResetPassword entry
     * @return Account the account, with id = -1 if the account does not exist
     */
    public function getAccountResetPasswordByToken(string $token): Account;


    /**
     * Updates the password of an account
     * @param Account $account the account to update with the new password
     */
    public function editAccountPassword(Account $account): void;


    /**
     * Deletes a ResetPassword entry
     * @param Account $account the account related to the ResetPassword entry to delete
     */
    public function deleteResetPassword(Account $account): void;
}
