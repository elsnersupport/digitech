<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Ui\DataProvider;

use Amasty\ShopByQuickConfig\Model\FilterCollectionBuilder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Ui\DataProvider\AbstractDataProvider;

class AddFiltersList extends AbstractDataProvider
{
    /**
     * @var FilterCollectionBuilder
     */
    private $collectionAdapterBuilder;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        FilterCollectionBuilder $collectionAdapterBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionAdapterBuilder = $collectionAdapterBuilder;
    }

    /**
     * Return collection
     *
     * @return AbstractCollection
     */
    public function getCollection(): AbstractCollection
    {
        if ($this->collection === null) {
            $this->collection = $this->collectionAdapterBuilder->build();
        }

        return $this->collection;
    }
}
