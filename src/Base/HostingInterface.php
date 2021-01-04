<?php

namespace Habib\Hosting\Base;

interface HostingInterface
{
    /**
     * Check connection with the hosting panel
     * @return integer result code
     */
    public function checkConnection();

    /**
     * Suspending user account in the hosting panel
     * @param string $userName
     * @return integer result code
     */
    public function suspendUser($userName);

    /**
     * Unsuspending user account in the hosting panel
     * @param string $userName
     * @return integer result code
     */
    public function unsuspendUser($userName);

    /**
     * Checking existence of the user account in the hosting panel
     * @param $userName
     * @return integer result code
     */
    public function userExist($userName);

    /**
     * Change user account password in the hosting panel
     * @param string $userName
     * @param string $newPassword
     * @return integer result code
     */
    public function changeUserPassword($userName, $newPassword);

    /**
     * Checking existence of the plan in the hosting panel
     * @param string $planName
     * @return integer result code
     */
    public function planExist($planName);


    /**
     * Get all plans of the hosting panel
     * @return array plans
     */
    public function getPlans();

    /**
     * @param string $userName
     * @param $newPlanName
     * @return integer result code
     */
    public function changePlan($userName, $newPlanName);


    /**
     * Creating new account in the hosting panel
     * @param array $data
     * @return integer result code
     */
    public function createUser($data);

    /**
     * Removing the user account in the hosting panel
     * @param string $userName
     * @return integer result code
     */
    public function removeUser($userName);
    /**
     * List all the accounts that the reseller has access to.
     *
     * @return mixed
     */
    public function listAccounts();

    /**
     * Create a new account
     *
     * @param $domain_name
     * @param $username
     * @param $password
     * @param $plan
     *
     * @return mixed
     */
    public function createAccount($domain_name, $username, $password, $plan);

    /**
     * This function deletes a cPanel or WHM account.
     *
     * @param string $username
     */
    public function destroyAccount($username);

    /**
     * Gets the email addresses that exist under a cPanel account
     *
     * @param $username
     */
    public function listEmailAccounts($username);

    /**
     * Gets the forwarders that exist under a cPanel account
     *
     * @param $username
     */
    public function listForwards($username);

    /**
     * @param $username **cPanel username**
     * @param $email email address to add
     * @param $password password **for the email address**
     * @return mixed
     * @throws \Exception
     */
    public function addEmailAccount($username, $email, $password);

    /**
     * Change the password for an email account in cPanel
     *
     * @param $username
     * @param $email
     * @param $password
     * @return mixed
     * @throws \Exception
     */
    public function changeEmailPassword($username, $email, $password);

}
