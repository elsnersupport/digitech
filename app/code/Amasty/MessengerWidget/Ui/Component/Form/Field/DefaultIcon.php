<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Live Chat for Magento 2
 */

namespace Amasty\MessengerWidget\Ui\Component\Form\Field;

use Amasty\MessengerWidget\Model\Messenger\Form\DefaultIcon\Mapper;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;

class DefaultIcon extends Field
{
    /**
     * @var Mapper
     */
    private $mapper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Mapper $mapper,
        array $components = [],
        array $data = []
    ) {
        $this->mapper = $mapper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare component configuration
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $this->_data['config']['imageMap'] = $this->mapper->getArrayDefaultImagesMap();
    }
}
