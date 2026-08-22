<?php
namespace HyvaElsner\CategoryListing\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\Category;

class CurrentCategory extends Template
{
    /**
     * Registry instance for storing shared data.
     *
     * @var Registry
     */
    protected $_registry;

    /**
     * Constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_registry = $registry;
    }

    /**
     * Get Current Category Function
     *
     * @return string
     */
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    /**
     * Get Category Short Description Function
     *
     * @return string
     */
    public function getSidebarPoster()
    {
        $category = $this->getCurrentCategory();
        if ($category) {
            return $category->getSidebarPoster();
        }
        return '';
    }
}
