<?php

declare(strict_types=1);

namespace Elsnertech\CurrencySymbol\Plugin;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\View\Design\Fallback\Rule\ModularSwitch as FallbackRule;
use Magento\Framework\App\State;

class FallbackRulePlugin
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;
 /**
     * @var State
     */
    private $appState;

    public function __construct(
        ComponentRegistrarInterface $componentRegistrar,
        State $appState
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->appState = $appState;
    }

    /**
     * Create a new fallback pattern with this module's web dir.
     *
     * This means that this modules email.less and other files are found when Magento is looking for global files
     * during static content deploy. There is no official way to overwrite theme files with module files.
     *
     * @param FallbackRule $subject
     * @param string[] $result
     * @param array $params
     * @return string[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
   public function afterGetPatternDirs(FallbackRule $subject, array $result, array $params): array
{
    try {
        $areaCode = $this->appState->getAreaCode();
    } catch (\Exception $e) {
        // Area code might not be available yet (early bootstrap/CLI)
        return $result;
    }
    
    // if (!isset($params['module_name'])) {
    //     $modulePath = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'Elsnertech_CurrencySymbol');

    //     if ($areaCode === 'frontend') {
    //         $result[] = $modulePath . '/view/frontend/web';
    //     } elseif ($areaCode === 'adminhtml' ) {
    //         $result[] = $modulePath . '/view/adminhtml/web';
    //     }else{
    //         $result[] = $modulePath . '/view/frontend/web';
    //     }
    // }
    return $result;
}

}
