<?php

namespace Buten\Viatec\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\ResourceModel\Config;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;

    /**
     * @var Config
     */
    protected Config $_resourceConfig;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Config $_resourceConfig
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Config $_resourceConfig
    ) {
        $this->_storeManager = $storeManager;
        $this->_resourceConfig = $_resourceConfig;
        parent::__construct($context);
    }

    /**
     * @param string $value
     * @return mixed
     */
    public function getConfigValue(string $path = ''): mixed
    {
        return $this->scopeConfig
            ->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string $value
     */
    public function setConfigValue(string $path, string $value = '')
    {
        $this->_resourceConfig->saveConfig($path, $value, 'default', 0);
    }
}
