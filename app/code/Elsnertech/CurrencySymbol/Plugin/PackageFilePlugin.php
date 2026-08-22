<?php

declare(strict_types=1);

namespace Elsnertech\CurrencySymbol\Plugin;

use Magento\Deploy\Package\Package;
use Magento\Deploy\Package\PackageFile;
use Magento\Framework\App\State;

class PackageFilePlugin
{
    /**
     * Handle all static files from this module as if they were in global scope.
     * This affects static content deployment.
     *
     * @param PackageFile $subject
     * @param Package $package
     * @return array
     */
     /**
     * @var State
     */
    private $appState;
    public function __construct(
        State $appState
    ) {
        $this->appState = $appState;
    }


    public function beforeSetPackage(PackageFile $subject, Package $package)
    {
        try {
        $areaCode = $this->appState->getAreaCode();
    } catch (\Exception $e) {
        // Area code might not be available yet (early bootstrap/CLI)
        return [$package];
    }
        // if ($subject->getModule() == 'Elsnertech_CurrencySymbol' ) {
        //     $subject->setModule('');
        // }
        return [$package];
    }
}
