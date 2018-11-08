<?php

namespace Smartsupp\Smartsupp\Controller\Adminhtml\Settings;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ProductMetadataInterface;
use Smartsupp\Smartsupp\Helper\Data;
use Smartsupp\Auth\Api;

/**
 * Index Controller Class.
 *
 * @category Class
 * @package  Smartsupp
 * @author   Smartsupp <vladimir@smartsupp.com>
 * @license  http://opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link     http://www.smartsupp.com
 */
class Index extends Action
{
    /**
     * Smartsupp partner key for Magento platform
     */
    const PARNER_KEY = '1p395yrdn9';

    const DOMAIN = 'smartsupp';

    const MSG_CACHE = 'Changes do not apply to Smartsupp plugin? Refresh Magento cache.',
        MSG_CACHE_GLOBAL = true; // show permanent message about cache refresh in plugin?

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ProductMetadataInterface $productMetadata
     * @param Data $dataHelper
     */
    public function __construct(Context $context, PageFactory $resultPageFactory, ProductMetadataInterface $productMetadata, Data $dataHelper)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->productMetadata = $productMetadata;
        $this->dataHelper = $dataHelper;
    }

	public function execute()
	{
		$formAction = $message = NULL;

        $ssaction = $this->getRequest()->getParam('ssaction');
        $email = $this->getRequest()->getParam('email');
        $password = $this->getRequest()->getParam('password');
        $code = $this->getRequest()->getParam('code');
        $termsConsent = $this->getRequest()->getParam('termsConsent');

        if (self::MSG_CACHE_GLOBAL) {
            $this->messageManager->addNotice(self::MSG_CACHE);
        }

		if (isset($ssaction)) {
			switch ($ssaction) {
				case 'login':
				case 'register':
					$formAction = $ssaction;
					$api = new Api;
					$data = array(
						'email' => $email,
						'password' => $password,
                        'consentTerms' => 1,
                        'platform' => 'Magento ' . $this->getMagentoVersion(),
                        'partnerKey' => self::PARNER_KEY,
					);
					try {
						$response = $formAction === 'login' ? $api->login($data) : $api->create($data + array(/*'partnerKey' => 'k717wrqdi5', */'lang' => 'en'));

						if (isset($response['error'])) {
							$message = $response['message'];
						} else {
							$this->activate($response['account']['key'], $email);
                            $message = self::MSG_CACHE;
						}
					} catch (Exception $e) {
						$message = $e->getMessage();
					}
					break;
				case 'update':
					$message = 'Custom code was updated. ' . self::MSG_CACHE;
					$this->updateOptions(array(
						'optionalCode' => (string) $code,
					));
					break;
				case 'disable':
					$this->deactivate();
                    $message = self::MSG_CACHE;
					break;
				default:
					$message = 'Invalid action';
					break;
			}
		}

        $resultPage = $this->resultPageFactory->create();
        $block = $resultPage->getLayout()->getBlock('smartsupp.settings');
        if ($block) {
            $block->setDomain(self::DOMAIN);
            $block->setFormAction($formAction);
            $block->setMessage($message);
            $block->setEmail($email ?: $this->_getOption('email'));
            $block->setActive((bool) $this->_getOption('active', false));
            $block->setOptionalCode($this->_getOption('optionalCode'));
            $block->setTermsConsent($termsConsent);
        }
        return $resultPage;
	}


	private function activate($chatId, $email)
	{
		$this->updateOptions(array(
			'active' => 1,
			'chatId' => (string) $chatId,
			'email' => (string) $email,
		));
	}


	private function deactivate()
	{
		$this->updateOptions(array(
			'active' => 0,
			'chatId' => null,
			'email' => null
		));
	}

    /**
     * Get option from Magento Smartsupp extension config.
     *
     * @param String $name    option name
     * @param String $default default value
     *
     * @return String
     */
    private function _getOption($name, $default = null)
    {
        $value = $this->dataHelper->getGeneralConfig($name);

        // if option is null (possibly not set in the past) return default value
        return !is_null($value) ? $value : $default;
    }


	private function updateOptions(array $options)
	{
		foreach ($options as $key => $value) {
            $this->dataHelper->setGeneralConfig($key, $value);
		}
	}

    private function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
