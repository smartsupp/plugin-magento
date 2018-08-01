<?php
namespace Smartsupp\Auth;

use Exception;
use Smartsupp\Auth\Request\CurlRequest;
use Smartsupp\Auth\Request\HttpRequest;

/**
 * Class to communicate with Smartsupp partner API.
 *
 * PHP version >=5.3
 *
 * @package    Smartsupp
 * @author     Marek Gach <gach@kurzor.net>
 * @copyright  since 2016 SmartSupp.com
 * @version    Git: $Id$
 * @link       https://github.com/smartsupp/php-partner-client
 */
class Api
{
    /** API call base URL */
    const API_BASE_URL = 'https://www.smartsupp.com/';

    /** URL paths for all used resources endpoints methods */
    const URL_LOGIN = 'account/login',
          URL_CREATE = 'account/create';

    /**
     * @var null|CurlRequest
     */
    private $handle = null;

    /**
     * Api constructor.
     *
     * @param null|HttpRequest $handle inject custom request handle to better unit test
     * @throws Exception
     */
    public function __construct(HttpRequest $handle = null)
    {
// @codeCoverageIgnoreStart
        if (!function_exists('curl_init')) {
            throw new Exception('Smartsupp API client needs the CURL PHP extension.');
        }
        if (!function_exists('json_decode')) {
            throw new Exception('Smartsupp API client needs the JSON PHP extension.');
        }
// @codeCoverageIgnoreEnd

        $this->handle = $handle ?: new CurlRequest();
    }

    /**
     * Allows to create user.
     *
     * @param array $data
     * @return array
     */
    public function create($data)
    {
        return $this->post(self::URL_CREATE, $data);
    }

    /**
     * Allows to log in account and obtain user key.
     *
     * @param array $data
     * @return array
     */
    public function login($data)
    {
        return $this->post(self::URL_LOGIN, $data);
    }

    /**
     * Helper function to execute POST request.
     *
     * @param string $path request path
     * @param array $data optional POST data array
     * @return array|string array data or json encoded string of result
     * @throws Exception
     */
    private function post($path, $data)
    {
        return $this->run($path, 'post', $data);
    }

    /**
     * Execute request against URL path with given method, optional data array. Also allows
     * to specify if json data will be decoded before function return.
     *
     * @param $path request path
     * @param $method request method
     * @param null|array $data optional request data
     * @param bool $json_decode specify if returned json data will be decoded
     * @return string|array JSON data or array containing decoded JSON data
     * @throws Exception
     */
    private function run($path, $method, $data = null, $json_decode = true)
    {
        $this->handle->setOption(CURLOPT_URL, self::API_BASE_URL . $path);
        $this->handle->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->handle->setOption(CURLOPT_FAILONERROR, false);
        $this->handle->setOption(CURLOPT_SSL_VERIFYPEER, true);
        $this->handle->setOption(CURLOPT_SSL_VERIFYHOST, 2);
        $this->handle->setOption(CURLOPT_USERAGENT, 'cURL:php-partner-client');

        switch ($method) {
            case 'post':
                $this->handle->setOption(CURLOPT_POST, true);
                $this->handle->setOption(CURLOPT_POSTFIELDS, $data);
                break;
        }

        $response = $this->handle->execute();

        if ($response === false) {
            throw new Exception($this->handle->getLastErrorMessage());
        }

        $this->handle->close();

        return $json_decode ? json_decode($response, true) : $response;
    }
}
