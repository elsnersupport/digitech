<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_GoogleTagManager
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GoogleTagManager\Block\Tag;

use Exception;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Mageplaza\GoogleTagManager\Block\TagManager;
use Mageplaza\GoogleTagManager\Helper\Data;
use Mageplaza\GoogleTagManager\Model\Config\Source\FacebookPixelEventList as FbEvent;

/**
 * Class PixelTag
 * @package Mageplaza\GoogleTagManager\Block\Tag
 */
class PixelTag extends TagManager
{

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getPixelId($storeId = null)
    {
        return $this->_helper->getConfigPixel('tag_id', $storeId);
    }

    /**
     * Can show pixel
     *
     * @return bool
     */
    public function canShowFbPixel()
    {
        return $this->_helper->isEnabled() && $this->_helper->getConfigPixel('enabled');
    }

    /**
     * @return array|null
     */
    public function getFbPageInfo()
    {
        try {
            $action = $this->getFullNameAction();
            switch ($action) {
                case 'cms_index_index':
                    return $this->getHomeData();
                case 'catalogsearch_result_index':
                case 'catalogsearch_advanced_result':
                    return $this->getSearchData();
                case 'catalog_product_view':
                    return $this->getProductView();
                case 'checkout_index_index':
                case 'checkout_cart_index':
                    return $this->getFBCheckoutProductData();
                case 'onestepcheckout_index_index':
                    return $this->_helper->moduleIsEnable('Mageplaza_Osc') ? $this->getFBCheckoutProductData() : null;
                case 'checkout_onepage_success':
                case 'multishipping_checkout_success':
                case 'mpthankyoupage_index_index': // Mageplaza Thank you page
                    return $this->getCheckoutSuccessData();
            }

            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getFullNameAction()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getHomeData()
    {
        $data ['ecommerce'] = [
            'currencyCode' => $this->_helper->getCurrentCurrency()
        ];

        return $data;
    }

    /**
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function getSearchData()
    {
        if (!in_array(FbEvent::SEARCH, $this->_helper->getShowFbPixelEvents())) {
            return [];
        }

        $productSearch = $this->_getProductCollection();
        if ($this->getFullNameAction() === 'catalogsearch_advanced_result') {
            $productSearch = $this->_getProductAdvancedCollection();
        }

        $sortDir         = $this->getRequest()->getParam('product_list_dir')
            ? $this->getRequest()->getParam('product_list_dir') : 'desc';
        $listFilterOrder = $this->getRequest()->getParam('product_list_order');
        if ($listFilterOrder) {
            $productSearch->addAttributeToSort($listFilterOrder, $sortDir);
        }
        $productSearch->setCurPage($this->getPageNumber())->setPageSize($this->getPageLimit());
        $products   = [];
        $productIds = [];
        $values     = 0;
        $storeId    = $this->_helper->getStoreId();
        $useIdOrSku = $this->getUseIdOrSku($storeId);

        foreach ($productSearch as $value) {
            $productIds[]    = $useIdOrSku ? $value->getSku() : $value->getId();
            $sub             = [];
            $sub['id']       = $useIdOrSku ? $value->getSku() : $value->getId();
            $sub['quantity'] = $this->_helper->getQtySale($value);
            $sub['name']     = $value->getName();
            $sub['price']    = $this->_helper->getPrice($value);
            $products[]      = $sub;
            $values          += $this->_helper->getPrice($value);
        }

        $data = [
            'track_type' => 'Search',
            'data'       => [
                'content_ids'  => $productIds,
                'content_name' => 'Search',
                'content_type' => 'product',
                'contents'     => $products,
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $values
            ]
        ];

        return $data;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getUseIdOrSku($storeId = null)
    {
        return $this->_helper->getConfigPixel('use_id_or_sku', $storeId);
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getProductView()
    {
        if (!in_array(FbEvent::VIEW_CONTENT, $this->_helper->getShowFbPixelEvents())) {
            return [];
        }

        $currentProduct = $this->_helper->getGtmRegistry()->registry('product');
        $fbData         = $this->_helper->getFBProductView($currentProduct);
        $data           = [
            'track_type' => 'ViewContent',
            'data'       => $fbData
        ];

        return $data;
    }

    /**
     * Get Checkout Data
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getFBCheckoutProductData()
    {
        $items      = $this->_cart->getQuote()->getAllVisibleItems();
        $products   = [];
        $productIds = [];
        $value      = 0;
        $storeId    = $this->_helper->getStoreId();
        $useIdOrSku = $this->getUseIdOrSku($storeId);

        if (empty($items) || !in_array(FbEvent::BEGIN_CHECKOUT, $this->_helper->getShowFbPixelEvents())) {
            return [];
        }

        foreach ($items as $item) {
            $productIds[] = $useIdOrSku ? $item->getSku() : $item->getProductId();
            $productInfo  = $this->_helper->getFBProductCheckOutData($item);
            $products[]   = $productInfo;
            $value        += $productInfo['price'] * $productInfo['quantity'];
        }

        $data = [
            'track_type' => 'InitiateCheckout',
            'data'       => [
                'content_ids'  => $productIds,
                'content_name' => 'checkout',
                'content_type' => 'product',
                'contents'     => $products,
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $value
            ]
        ];

        return $data;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    protected function getCheckoutSuccessData()
    {
        $data  = [];
        $order = $this->_helper->getSessionManager()->getLastRealOrder();

        //handle multiple shipping case
        if ($this->isMultiShipping()) {
            $orderIds       = $this->getMultiShipping()->getOrderIds();
            $baseGrandTotal = 0;
            $i              = 0;
            if ($orderIds) {
                foreach ($orderIds as $orderId) {
                    /** @var Order $or */
                    $or                          = $this->orderRepository->get($orderId);
                    $baseGrandTotal              += $this->_helper->calculateTotals($or);

                    if ($or->getPayment()->getMethodInstance()->getCode() != 'free'
                    && in_array(FbEvent::PAYMENT, $this->_helper->getShowFbPixelEvents())) {
                        $data[$i] = $this->getPaymentDataEachOrder($or);
                    }
                    $i++;

                    $data[$or->getIncrementId()] = $this->getCheckoutSuccessDataEachOrder($or);
                }
            }

            if ($this->_helper->isEnabledIgnoreOrders($this->_helper->getStoreId()) && $baseGrandTotal <= 0) {
                return [];
            }

            return $data;
        }

        if ($this->_helper->isEnabledIgnoreOrders($this->_helper->getStoreId())
            && $this->_helper->calculateTotals($order) <= 0) {
            return [];
        }

        if ($order->getPayment()->getMethodInstance()->getCode() != 'free'
        && in_array(FbEvent::PAYMENT, $this->_helper->getShowFbPixelEvents())) {
            $data['payment'] = $this->getPaymentDataEachOrder($order);
        }

        $data['purchase'] = $this->getCheckoutSuccessDataEachOrder($order);

        return $data;
    }

    /**
     * @param Order $order
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getCheckoutSuccessDataEachOrder($order)
    {
        if (!in_array(FbEvent::PURCHASE, $this->_helper->getShowFbPixelEvents())) {
            return [];
        }

        $products   = [];
        $productIds = [];
        $storeId    = $this->_helper->getStoreId();
        $useIdOrSku = $this->getUseIdOrSku($storeId);
        $items      = $order->getItemsCollection([], true);
        foreach ($items as $item) {
            $productIds[] = $useIdOrSku ? $item->getSku() : $item->getProductId();
            $products[]   = $this->_helper->getFBProductOrderedData($item);
        }

        $data = [
            'track_type' => 'Purchase',
            'data'       => [
                'content_ids'  => $productIds,
                'content_name' => 'Purchase',
                'content_type' => 'product',
                'contents'     => $products,
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $this->_helper->calculateTotals($order)
            ]
        ];

        return $data;
    }

    /**
     * @param $order
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getPaymentDataEachOrder($order)
    {
        $data = [
            'track_type' => 'AddPaymentInfo',
            'data'       => [
                'content_ids'  => $order->getPayment()->getMethodInstance()->getCode(),
                'content_name' => 'Purchase',
                'content_type' => 'payment method',
                'contents'     => ['title' => $order->getPayment()->getMethodInstance()->getTitle()],
                'currency'     => $this->_helper->getCurrentCurrency(),
                'value'        => $this->_helper->calculateTotals($order)
            ]
        ];

        return $data;
    }

    /**
     * @return false|string|null
     */
    public function getFBAddToCartData()
    {
        if ($this->_helper->getSessionManager()->getFBAddToCartData()) {
            return json_encode($this->_helper->getSessionManager()->getFBAddToCartData());
        }

        return null;
    }

    /**
     * Get Facebook Pixel Advanced Matching Data
     *
     * @return array|null
     */
    public function getAdvancedMatchingData()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            $customer               = $customerSession->getCustomer();
            $defaultShippingAddress = $customer->getDefaultShippingAddress();

            $advancedMatchingData = [
                'em' => strtolower($customer->getEmail()),
                'fn' => strtolower($customer->getFirstname()),
                'ln' => strtolower($customer->getLastname()),
                'ge' => $customer->getGender() != 3 ? ($customer->getGender() == 2 ? 'f' : 'm') : '',
            ];

            if ($customer->getDob()) {
                $advancedMatchingData['db'] = str_replace('-', '', $customer->getDob());
            }

            if ($defaultShippingAddress) {
                $advancedMatchingData['country'] = strtolower($defaultShippingAddress->getCountryId());
                $advancedMatchingData['ct']      = strtolower($defaultShippingAddress->getCity());
                $advancedMatchingData['st']      = strtolower($defaultShippingAddress->getRegionCode() ?? '');
                $advancedMatchingData['zp']      = strtolower($defaultShippingAddress->getPostcode());
                $advancedMatchingData['ph']      = preg_replace(
                    '~^(\+)|\D~',
                    '\1',
                    $defaultShippingAddress->getTelephone()
                );
            }

            return $advancedMatchingData;
        }

        return null;
    }

    /**
     * @return Data
     */
    public function getHelperData()
    {
        return $this->_helper;
    }

    /**
     * @return string
     */
    public function renderPixelTagHead()
    {
        $html = '';
        $pixelId = $this->getPixelId();
        if ($this->getHelperData()->getConfigPixel('advanced_matching')
        && $this->getAdvancedMatchingData()) {
            $html .= $this->secureRenderer->renderTag(
                'script',
                ['type' => 'text/javascript'],
                <<<script
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{$this->_escaper->escapeHtmlAttr($pixelId)}', {$this->encodeJs($this->getAdvancedMatchingData())});
            fbq('track', "PageView");
script
                ,
                false
            );
        } else {
            $html .= $this->secureRenderer->renderTag(
                'script',
                ['type' => 'text/javascript'],
                <<<script
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{$this->_escaper->escapeHtmlAttr($pixelId)}');
            fbq('track', "PageView");
script
                ,
                false
            );
        }

        $html .= $this->secureRenderer->renderTag(
            'noscript',
            [],
            <<<noscript
            <img height="1" width="1" style="display:none"
             alt="Facebook Pixel"
             src="https://www.facebook.com/tr?id={$this->_escaper->escapeHtmlAttr($pixelId)}&ev=PageView&noscript=1"/>
noscript
            ,
            false
        );

        return $html;
    }

    /**
     * @return string
     */
    public function renderPixelTagBody()
    {
        $html = '';
        $script = '';
        $fbData = $this->getFbPageInfo();
        if ($this->isMultiShipping()) {
            foreach ($fbData as $dt) {
                if (isset($dt['track_type'])) {
                    $script .=
                    <<<script
fbq(
                        'track',
                        '{$this->_escaper->escapeHtml($dt['track_type'])}',
                        {$this->_escaper->escapeHtml($this->encodeJs($dt['data']))});
script;
                }
            }
        } else {
            if ($this->getFullNameAction() == 'checkout_onepage_success') {
                foreach ($fbData as $data) {
                    if (isset($data['track_type'])) {
                        $script .=
                            <<<script
fbq(
                        'track',
                        '{$data['track_type']}',
                        {$this->encodeJs($data['data']) });
script;
                    }
                }
            } else {
                if (isset($fbData['track_type'])) {
                    $script .=
                        <<<script
fbq(
                        'track',
                        '{$fbData['track_type']}',
                        {$this->encodeJs($fbData['data']) });
script;
                }
            }
        }

        if ($script != "") {
            $html .= $this->secureRenderer->renderTag('script', [], $script, false);
        }

        return $html;
    }
}
