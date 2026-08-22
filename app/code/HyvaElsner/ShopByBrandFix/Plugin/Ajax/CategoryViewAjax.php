<?php


namespace HyvaElsner\ShopByBrandFix\Plugin\Ajax;

use HyvaElsner\ShopByBrandFix\Model\Ajax\AjaxResponseBuilder;
use Amasty\Shopby\Model\Ajax\Counter\CounterDataProvider;
use Amasty\Shopby\Model\Ajax\RequestResponseUtils;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;

class CategoryViewAjax
{
    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var RequestResponseUtils
     */
    private $utils;

    /**
     * @var AjaxResponseBuilder
     */
    private $ajaxResponseBuilder;

    /**
     * @var CounterDataProvider
     */
    private $counterDataProvider;

    public function __construct(
        ActionFlag $actionFlag,
        RequestResponseUtils $utils,
        AjaxResponseBuilder $ajaxResponseBuilder,
        CounterDataProvider $counterDataProvider
    ) {
        $this->actionFlag = $actionFlag;
        $this->utils = $utils;
        $this->ajaxResponseBuilder = $ajaxResponseBuilder;
        $this->counterDataProvider = $counterDataProvider;
    }

    public function beforeExecute(Action $controller): void
    {
        if ($this->utils->isAjaxNavigation($controller->getRequest())) {
            $this->actionFlag->set('', 'no-renderLayout', true);
        }
    }

    /**
     * @param Action $controller
     * @param Page $page
     *
     * @return Raw|Page
     */
    public function afterExecute(Action $controller, $page)
    {
        if (!$this->isAjaxNavigation($controller->getRequest())) {
            return $page;
        }


        $responseData = $this->ajaxResponseBuilder->build();

        return $this->utils->prepareResponse($responseData);
    }
    public function isAjaxNavigation(RequestInterface $request): bool
    {
        if (!$request instanceof Http) {
            return false;
        }
        $isAjax = $request->isXmlHttpRequest() && $request->isAjax() && $request->getParam('shopbyAjax', false);
        $isScroll = $request->getParam('is_scroll');
        return $isAjax && !$isScroll;
    }
}
