<?php

namespace Smartsupp\Smartsupp\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Framework\UrlInterface;

/**
 * SmartsuppBlock Template Class.
 *
 * @category Class
 * @package  Smartsupp
 * @author   Smartsupp <vladimir@smartsupp.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.smartsupp.com
 */
class SmartsuppBlock extends Template
{

	/** @var string */
	private $baseUrl;

	private static $formAction;

	private static $message;

	private static $email;

	private static $options;

	public function __construct(Template\Context $context, array $data = [])
	{
		parent::__construct($context, $data);

		// @todo: do not rely on this, will not work in production mode
		$this->baseUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . 'smartsupp';
	}

    // @todo: do not rely on this, will not work in production mode
	public function getBaseUrl()
	{
		return $this->baseUrl;
	}


	public static function setVars($formAction, $message, $email, $options)
	{
		self::$formAction = $formAction;
		self::$message = $message;
		self::$email = $email;
		self::$options = $options;
	}


	/**
	 * @return mixed
	 */
	public function getFormAction()
	{
		return self::$formAction;
	}


	/**
	 * @return mixed
	 */
	public function getMessage()
	{
		return self::$message;
	}


	/**
	 * @return mixed
	 */
	public function getEmail()
	{
		return self::$email;
	}


	public function getOptions()
	{
		return self::$options;
	}

}
