<?php

namespace Smartsupp\Smartsupp\Block\Frontend;

use Magento\Backend\Block\Template;
use \Magento\Backend\Block\Template\Context;
use Smartsupp\Smartsupp\Helper\Data;
use Magento\Framework\App\ProductMetadataInterface;

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

    public function __construct(
        Context $context,
        array $data = [],
        ProductMetadataInterface $productMetadata,
        Data $dataHelper
    ) {
        parent::__construct($context, $data);
        $this->productMetadata = $productMetadata;
        $this->dataHelper = $dataHelper;
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
        return !is_null($value) ? $value : $default;
    }

    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
