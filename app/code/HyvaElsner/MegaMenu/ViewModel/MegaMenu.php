<?php

declare(strict_types=1);

namespace HyvaElsner\MegaMenu\ViewModel;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Hyva\Theme\ViewModel\SvgIcons;
use HyvaElsner\Base\Model\Config as BaseConfig;
use HyvaElsner\Base\ViewModel\StoreInformation;

class MegaMenu implements ArgumentInterface
{
    public const MENU_DATA = 'hyva_mega_menu/menu_configuration/data';

    /** @var BaseConfig */
    protected $baseConfig;

    /** @var StoreInformation */
    protected $storeInformation;

    /** @var Json */
    protected $serialize;

    /** @var SvgIcons */
    protected $svgIcons;
    protected $storeInfo;
    /**
     * Constructor function
     *
     * @param BaseConfig $baseConfig
     * @param StoreInformation $storeInformation
     * @param Json $serialize
     * @param SvgIcons $svgIcons
     */
    public function __construct(
        BaseConfig $baseConfig,
        StoreInformation $storeInformation,
        Json $serialize,
        SvgIcons $svgIcons
    ) {
        $this->baseConfig = $baseConfig;
        $this->storeInfo = $storeInformation;
        $this->serialize = $serialize;
        $this->svgIcons = $svgIcons;
    }

    public function getMenuData(): array
    {
        $menuData = [];
        try {
            $leftMenu = $this->baseConfig->getConfigValue(self::MENU_DATA, ScopeInterface::SCOPE_STORE);
            if ($leftMenu == '' || $leftMenu == null) {
                return [];
            }

            $unserializedata = $this->serialize->unserialize($leftMenu);
            foreach ($unserializedata as $row) {
                try {
                    $svg = $this->svgIcons->renderHtml($row['svgName'], 'top-icon', 26, 26, ["aria-hidden" => "true"]);
                } catch (\Exception $e) {
                    $svg = "";
                }
                $menuData[] = [
                    'label' => $row['label'],
                    'url' => $row['url'],
                    'html_class' => $row['htmlClass'],
                    'svg_image_name' => $svg
                ];
            }
            return $menuData;
        } catch (NoSuchEntityException $e) {
            return [];
        }
    }
}
