<?php

namespace HyvaElsner\Base\Block;

use Magento\Framework\App\View\Deployment\Version\StorageInterface;

class CssLoad extends \Magento\Framework\View\Element\Template
{
    protected $deployedVersion;

    /**
     * constructor
     * @param StorageInterface $storage
     */
    public function __construct(
        StorageInterface $storage,
        \Magento\Framework\View\Element\Template\Context $context
    ) {
        $this->storage = $storage;
        parent::__construct($context);
    }

    public function getDeploymentId()
    {
        if (!isset($this->deployedVersion)) {
            $this->deployedVersion = $this->storage->load();
        }

        return $this->deployedVersion;
    }
}
