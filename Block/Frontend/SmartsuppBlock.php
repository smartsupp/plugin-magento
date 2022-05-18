<?php

namespace Smartsupp\Smartsupp\Block\Frontend;

use Magento\Backend\Block\Template;
use \Magento\Backend\Block\Template\Context;
use Smartsupp\Smartsupp\Helper\Data;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;

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
    /**
     * @var Data
     */
    protected $dataHelper;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        Data $dataHelper,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productMetadata = $productMetadata;
        $this->dataHelper = $dataHelper;
        $this->objectManager = $objectManager;
    }

    /**
     * Get option from Magento Smartsupp extension config.
     *
     * @param String $name    option name
     * @param String $default default value
     *
     * @return String
     */
    public function _getOption($name, $default = null)
    {
        $value = $this->dataHelper->getGeneralConfig($name);

        // if option is null (possibly not set in the past) return default value
        return $value !== null ? $value : $default;
    }

    /**
     * @see https://github.com/magento/magento2/issues/3294 for most reliable way we use compatible with Magento 2.0+
     * @return mixed
     */
    public function getCustomerSession() {
        return $this->objectManager->create('Magento\Customer\Model\SessionFactory')->create();
    }

    /**
     * Load Magento version
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
