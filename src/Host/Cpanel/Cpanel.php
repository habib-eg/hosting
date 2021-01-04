<?php


namespace Habib\Hosting\Host\Cpanel;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Habib\Hosting\Base\Hosting;
use Habib\Hosting\Base\HostingInterface;

class Cpanel extends Hosting implements HostingInterface
{

    /**
     * List all the accounts that the reseller has access to.
     *
     * @return mixed
     */
    public function listAccounts()
    {
        return $this->execute('listaccts', []);
    }

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
    public function createAccount($domain_name, $username, $password, $plan)
    {
        return $this->execute('createacct', [
            'username' => $username,
            'domain' => $domain_name,
            'password' => $password,
            'plan' => $plan,
        ]);
    }

    /**
     * This function deletes a cPanel or WHM account.
     *
     * @param string $username
     */
    public function destroyAccount($username)
    {
        return $this->execute('removeacct', [
            'username' => $username,
        ]);
    }

    /**
     * Gets the email addresses that exist under a cPanel account
     *
     * @param $username
     */
    public function listEmailAccounts($username)
    {
        return $this->cpanel('Email', 'listpops', $username);
    }

    /**
     * Gets the forwarders that exist under a cPanel account
     *
     * @param $username
     */
    public function listForwards($username)
    {
        return $this->cpanel('Email', 'listforwards', $username);
    }

    /**
     * @param $username **cPanel username**
     * @param $email **email address to add**
     * @param $password **password **for the email address**
     * @return mixed
     * @throws \Exception
     */
    public function addEmailAccount($username, $email, $password)
    {
        list($account, $domain) = $this->split_email($email);

        return $this->emailAction('addpop', $username, $password, $domain, $account);
    }

    /**
     * Change the password for an email account in cPanel
     *
     * @param $username
     * @param $email
     * @param $password
     * @return mixed
     * @throws \Exception
     */
    public function changeEmailPassword($username, $email, $password)
    {
        list($account, $domain) = $this->split_email($email);

        return $this->emailAction('passwdpop', $username, $password, $domain, $account);
    }

    /**
     * Runs a blank API Request to pull cPanel's response.
     *
     * @return array [status (0 is fail, 1 is success), error (internal error code), verbose (Extended error message)]
     */
    public function checkConnection()
    {
        try {
            $this->execute('', [], true);
        } catch (\Exception $e) {
            if ($e->hasResponse()) {
                switch ($e->getResponse()->getStatusCode()) {
                    case 403:
                        return [
                            'status' => 0,
                            'error' => 'auth_error',
                            'verbose' => 'Check Username and Password/Access Key.'
                        ];
                    default:
                        return [
                            'status' => 0,
                            'error' => 'unknown',
                            'verbose' => 'An unknown error has occurred. Server replied with: ' . $e->getResponse()->getStatusCode()
                        ];
                }
            } else {
                return [
                    'status' => 0,
                    'error' => 'conn_error',
                    'verbose' => 'Check CSF or hostname/port.'
                ];
            }
            return false;
        }

        return [
            'status' => 1,
            'error' => false,
            'verbose' => 'Everything is working.'
        ];
    }

    /**
     * Split an email address into two items, username and host.
     *
     * @param $email
     * @return array
     * @throws \Exception
     */
    private function split_email($email)
    {
        $email_parts = explode('@', $email);
        if (count($email_parts) !== 2) {
            throw new \Exception("Email account is not valid.");
        }

        return $email_parts;
    }

    /**
     * Perform an email action
     *
     * @param $action
     * @param $username
     * @param $password
     * @param $domain
     * @param $account
     * @return mixed
     */
    public function emailAction($action, $username, $password, $domain, $account)
    {
        return $this->cpanel('Email', $action, $username, [
            'domain' => $domain,
            'email' => $account,
            'password' => $password,
        ]);
    }

    /**
     * Extend HTTP headers that will be sent.
     *
     * @return self list of headers that will be sent
     *
     * @since v1.0.0
     */
    protected function addAuthorization()
    {

        $username = $this->getUsername();
        $auth_type = $this->getAuthType();
        $this->addHeader([
            'Authorization' => ( $auth_type ==  'hash')
                ? 'WHM ' . $username . ':' . preg_replace("'(\r|\n|\s|\t)'", '', $this->getPassword())
                :
                (
                ( $auth_type ==  'password')
                    ?  'Basic ' . base64_encode($username . ':' .$this->getPassword())
                    : null
                )
        ]);

        return $this;
    }

    /**
     * The executor. It will run API function and get the data.
     *
     * @param string $action function name that will be called.
     * @param array $arguments list of parameters that will be attached.
     * @param bool   $throw defaults to false, if set to true rethrow every exception.
     *
     * @return string|array results of API call
     *
     * @throws Exception|ClientException|\GuzzleHttp\Exception\GuzzleException
     *
     * @since v1.0.0
     */
    protected function execute($action, $arguments = [], $throw = false)
    {
        $this->addAuthorization();
        $client = $this->getHttp()->baseUrl($this->getHostName());
        try{
            $response = $client->post('/json-api/' . $action, [
                'headers' => $this->getHeaders(),
                'verify' => false,
                'query' => $arguments,
                'timeout' => $this->getHeaders()['timeout'] ?? 0,
                'connect_timeout' => $this->getHeaders()['connect_timeout'] ?? 0
            ]);
            if (($decodedBody = json_decode($response->getBody(), true)) === false) {
                throw new \Exception(json_last_error_msg(), json_last_error());
            }

            return $decodedBody;
        }
        catch(\GuzzleHttp\Exception\ClientException $e)
        {
            if ($throw) {
                throw $e;
            }
            return $e->getMessage();
        }
    }

    /**
     * Use a cPanel API
     *
     * @param string $module
     * @param string $function
     * @param string $username
     * @param array $params
     * @return mixed
     * @throws \Exception|\GuzzleHttp\Exception\GuzzleException
     */
    public function cpanel($module, $function, $username, $params = [])
    {
        $action = 'cpanel';
        $params = array_merge($params, [
            'cpanel_jsonapi_version' => 2,
            'cpanel_jsonapi_module' => $module,
            'cpanel_jsonapi_func' => $function,
            'cpanel_jsonapi_user' => $username,
        ]);

        $response = $this->execute($action, $params);
        return $response;
    }

    /**
     * Use cPanel API 1 or use cPanel API 2 or use UAPI.
     *
     * @param $api (1 = cPanel API 1, 2 = cPanel API 2, 3 = UAPI)
     * @param $module
     * @param $function
     * @param $username
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function execute_action($api, $module, $function, $username, $params = array())
    {
        $action = 'cpanel';
        $params = array_merge($params, [
            'cpanel_jsonapi_apiversion' => $api,
            'cpanel_jsonapi_module' => $module,
            'cpanel_jsonapi_func' => $function,
            'cpanel_jsonapi_user' => $username,
        ]);
        $response = $this->execute($action, $params);

        return $response;
    }

    public function changeUserPassword($user, $password){
        $this->query .= 'passwd?'. http_build_query(array('user' => $user, 'password' => $password));
        if ($this->exec()) {
            print_r($this->object);
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            }

            if (preg_match('/(.*) passwords must be at least (.*)/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_PASSWORD_NOT_VALID;
            }
            if (preg_match('/(.*) the user (.*) does not exist./', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_NOT_EXIST;
            }
            if (preg_match("/(.*) the password you selected cannot be used (.*)/", $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_PASSWORD_NOT_VALID;
            }

            return HostingAPI::ANSWER_SYSTEM_ERROR;

        } else {
            return $this->execError;
        }
    }

    public function userExist($user)
    {
        $this->query .= 'accountsummary?' . http_build_query(array('user' => $user));

        if ($this->exec()) {
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_USER_EXIST;
            } else {
                return HostingAPI::ANSWER_USER_NOT_EXIST;
            }
        } else {
            return $this->execError;
        }
    }

    public function planExist($name)
    {

        $this->query .= 'getpkginfo?' . http_build_query(array('pkg' => $name));

        if ($this->exec()) {

            if (isset($this->object->data->pkg)) {
                return HostingAPI::ANSWER_PLAN_EXIST;
            } else {
                return HostingAPI::ANSWER_PLAN_NOT_EXIST;
            }
        } else {
            return $this->execError;
        }

    }

    public function createUser($data)
    {

        $this->query .= 'createacct?' . http_build_query(array(
                    'username'     => $data['username'],
                    'domain'       => $data['domain'],
                    'plan'         => $data['package'],
                    'password'     => $data['password'],
                    'contactemail' => $data['email'],
                )
            );

        if ($this->exec()) {

            //  print_r($this->object);
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            }
            // print_r($this->object);
            if (preg_match('/(.*) is not a valid username on this system./', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_NAME_NOT_VALID;
            }
            if (preg_match('/(.*) passwords must be at least (.*)/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_PASSWORD_NOT_VALID;
            }
            if (preg_match("/(.*) the password you selected cannot be used (.*)/", $this->object->metadata->reason) ||
                preg_match("/(.*)password may not contain the username for security reasons(.*)/", $this->object->metadata->reason)

            ) {
                return HostingAPI::ANSWER_USER_PASSWORD_NOT_VALID;
            }

            if (preg_match("/(.*) system already has an account (.*)/", $this->object->metadata->reason)
            ) {
                return HostingAPI::ANSWER_USER_ALREADY_EXIST;
            }


            if (preg_match('/The name of another account on this server has the same initial (.*)/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_NAME_NOT_VALID;
            }


            if (preg_match('/(.*) domain (.*) already exists(.*)/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_DOMAIN_ALREADY_EXIST;
            }

            $this->_error = $this->object->metadata->reason;

            return HostingAPI::ANSWER_SYSTEM_ERROR;

        } else {
            return $this->execError;
        }
    }

    public function suspendUser($user)
    {
        $this->query .= 'suspendacct?' . http_build_query(array('user' => $user));
        if ($this->exec()) {
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            } else {
                return HostingAPI::ANSWER_SYSTEM_ERROR;
            }
        } else {
            return $this->execError;
        }
    }

    public function unsuspendUser($user)
    {
        $this->query .= 'unsuspendacct?' . http_build_query(array('user' => $user));
        // $this->exec();
        if ($this->exec()) {
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            } else {
                return HostingAPI::ANSWER_SYSTEM_ERROR;
            }
        }

        return $this->execError;
    }

    public function removeUser($user)
    {
        $this->query .= 'removeacct?' . http_build_query(array('username' => $user));


        if ($this->exec()) {

            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            }

            return HostingAPI::ANSWER_SYSTEM_ERROR;
        }

        return $this->execError;
    }

    public function changePlan($user, $plan)
    {
        $this->query .= 'changepackage?' . http_build_query(array('user' => $user, 'pkg' => $plan));
        if ($this->exec()) {
            if ($this->object->metadata->result) {
                return HostingAPI::ANSWER_OK;
            }

            if (preg_match('/Sorry the user (.*) does not exist/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_USER_NOT_EXIST;
            }
            if (preg_match('/Specified package (.*) does not exist/', $this->object->metadata->reason)) {
                return HostingAPI::ANSWER_PLAN_NOT_EXIST;
            }

            return HostingAPI::ANSWER_SYSTEM_ERROR;
        } else {
            return $this->execError;
        }
    }

    public function getPlans()
    {
        $this->query .= 'listpkgs?';
        $this->exec();

        $obj   = $this->object;

        $plans = array();

        if (isset($obj->data->pkg)) {
            foreach ($obj->data->pkg as $elem) {
                $plans[$elem->name] = $elem->name;
            }
        }

        return $plans;

    }

    private function exec()
    {

        $this->query .= '&api.version=1';
        // echo $this->hostname . $this->query;
        $this->execError = null;
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);       // Allow self-signed certs
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, 0);       // Allow certs that do not match the hostname
        curl_setopt($this->curl, CURLOPT_HEADER, 0);               // Do not include header in output
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);       // Return contents of transfer on curl_exec
        $header[0] = "Authorization: Basic " . base64_encode($this->username . ":" . $this->password) . "\n\r";
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
        curl_setopt($this->curl, CURLOPT_URL, $this->hostname . $this->query);            // execute the query

        $this->res    = curl_exec($this->curl);
        $this->object = json_decode($this->res);

        //  echo curl_error($this->curl);

        // echo $this->res;

        curl_close($this->curl);


        $this->query = '';

        if ($this->res == '' || (isset($this->object->cpanelresult->data->reason) && $this->object->cpanelresult->data->reason == 'Access denied')) {
            $this->execError = HostingAPI::ANSWER_CONNECTION_ERROR;

            return false;
        }

        // print_r($this->object);

        return true;
    }

}
