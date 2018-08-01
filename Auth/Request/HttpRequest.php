<?php
namespace Smartsupp\Auth\Request;

/**
 * Interface HttpRequest serves as base interface for concrete Request implementations.
 *
 * @package Smartsupp\Request
 */
interface HttpRequest
{
    /**
     * Allows to set request options.
     *
     * @param string $name option name
     * @param string $value option value
     */
    public function setOption($name, $value);

    /**
     * Execute request call.
     *
     * @return boolean execution status
     */
    public function execute();

    /**
     * Get request status info.
     *
     * @param int $opt options
     * @return array status info array
     */
    public function getInfo($opt = 0);

    /**
     * Close request connection.
     *
     * @return boolean close status
     */
    public function close();

    /**
     * Get last error message as formated string.
     *
     * @return string formated error message
     */
    public function getLastErrorMessage();
}
