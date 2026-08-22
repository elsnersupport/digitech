<?php
namespace HyvaElsner\ShopbybrandFix\Plugin;

use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Mageplaza\Shopbybrand\Helper\Data;
use Magento\Framework\App\RequestInterface;

class ToolbarMemorizerPlugin
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    public function __construct(Data $helperData, RequestInterface $request,  \Magento\Framework\Module\Manager $moduleManager)
    {
        $this->request = $request;
        $this->helperData = $helperData;
        $this->moduleManager = $moduleManager;
    }

    /**
     * After plugin to override the isMemorizingAllowed method
     *
     * @param ToolbarMemorizer $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsMemorizingAllowed(ToolbarMemorizer $subject, $result)
    {
        $isModuleEnabled = $this->moduleManager->isEnabled('Amasty_ShopbyHyvaCompatibility');
        if ($isModuleEnabled) {
            return $result;
        }
        if (array_key_exists('brand_key', $this->request->getParams()))
        {
            return false;
        }
        return true;
    }
}
