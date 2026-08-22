<?php

namespace Elsnertech\Extradescription\Block\Product;

class Review extends \Magento\Review\Block\Product\Review
{
    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $title = $this->getCollectionSize()
            ? __('Product Reviews (%1', '<span class="counter">' . $this->getCollectionSize() . '</span>)')
            : __('Product Reviews');
        $this->setTitle($title);
    }
}