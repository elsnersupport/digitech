<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Block\Adminhtml\Attribute;

use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Frontend\Inputtype\Presentation;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class DataProvider implements ArgumentInterface
{
    /**
     * Registry is Deprecated, used for emulate Magento edit attribute behavior.
     *
     * @var Registry
     */
    private $registry;

    /**
     * @var Presentation
     */
    private $presentation;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(
        Registry $registry,
        Presentation $presentation,
        ProductAttributeRepositoryInterface $attributeRepository,
        RequestInterface $request
    ) {
        $this->registry = $registry;
        $this->presentation = $presentation;
        $this->attributeRepository = $attributeRepository;
        $this->request = $request;

        $this->init();
    }

    /**
     * Emulate standard Magento attribute edit behavior.
     *
     * @throws LocalizedException
     */
    public function init(): void
    {
        $params = $this->request->getParams();
        if (!isset($params['attribute_code'])) {
            throw new LocalizedException(__('Parameter attribute_code is required'));
        }
        $attributeCode = $params['attribute_code'];

        $model = $this->attributeRepository->get($attributeCode);
        $params['attribute_id'] = $model->getAttributeId();
        $this->request->setParams($params);

        $model->setFrontendInput($this->presentation->getPresentationInputType($model));

        /**
         * Emulate standard Magento attribute edit behavior.
         * @see \Magento\Catalog\Controller\Adminhtml\Product\Attribute\Edit::execute
         */
        $this->registry->register('entity_attribute', $model);
    }
}
