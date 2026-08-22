<?php

declare(strict_types=1);

namespace HyvaElsner\Base\ViewModel;

use Magento\Directory\Model\Currency as CurrencyModel;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Bundle\CurrencyBundle;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use HyvaElsner\Base\Model\Config as ConfigModel;

class Config implements ArgumentInterface
{
    /** @var CurrencyModel */
    protected $currencyModel;

    /** @var ConfigModel */
    protected $configModel;

    /**
     * Constructor
     *
     * @param CurrencyModel $currencyModel
     * @param ConfigModel $configModel
     */
    public function __construct(
        CurrencyModel $currencyModel,
        ConfigModel $configModel
    ) {
        $this->currencyModel = $currencyModel;
        $this->configModel = $configModel;
    }

    /**
     * Get Currency Symbol
     *
     * @param string $currencyCode The currency code
     * @return string
     */
    public function getCurrencySymbol($currencyCode)
    {
        return $this->currencyModel->load($currencyCode)->getCurrencySymbol();
    }

    /**
     * Get Currency Symbol
     *
     * @param string $currencyCode The currency code
     * @return string
     */
    public function getCurrencyLogo($currencyCode)
    {
        return $this->configModel->getMediaUrl()."logo/flag/".$currencyCode.".png";
    }
}
