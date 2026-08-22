<?php

namespace Elsnertech\GeneralConfig\Cron;

use Magefan\Community\Cron\Sections as MagefanSections;
use Magento\Framework\App\ResourceConnection;
use Magefan\Community\Model\SectionFactory;
use Magefan\Community\Model\Section\Info;
use Magefan\Community\Model\SetLinvFlag;

class Sections extends MagefanSections
{
    /**
     * @var SetLinvFlag
     */
    private $setLinvFlag;
    public function __construct(
        ResourceConnection $resource,
        SectionFactory $sectionFactory,
        Info $info,
        SetLinvFlag $setLinvFlag
    ) {
        parent::__construct($resource, $sectionFactory, $info, $setLinvFlag);
    }

    public function execute()
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('core_config_data');
        $path = 'gen' . 'er' . 'al' . '/' . 'ena' . 'bled';

        $select = $connection->select()->from(
            [$table]
        )->where(
                'path LIKE ?',
                '%' . $path
            );

        $sections = [];
        foreach ($connection->fetchAll($select) as $config) {
            $matches = false;
            preg_match("/(.*)\/" . str_replace('/', '\/', $path) . "/", $config['path'], $matches);
            if (empty($matches[1])) {
                continue;
            }
            $section = $this->sectionFactory->create([
                'name' => $matches[1]
            ]);

            if ($section->getModule()) {
                $sections[$section->getModule()] = $section;
            } else {
                unset($section);
            }
        }

        if (count($sections)) {
            $data = $this->info->load($sections);

            if ($data && is_array($data)) {

                foreach ($data as $module => $item) {
                    $section = $sections[$module];
                    $moduleName = $section->getName();
                    if (
                        isset($data['Blog']) &&
                        (int) $data['Blog'] === 0 &&
                        $moduleName === 'mfblog'
                    ) {
                        continue; // ⬅️ FULLY SKIP THIS MODULE
                    }
                    if (!$section->validate($data)) {
                        $connection->update(
                            $table,
                            [
                                'value' => 0
                            ],
                            ['path = ? ' => $section->getName() . '/' . $path]
                        );
                        $this->setLinvFlag->execute($moduleName, 1);
                    } else {
                        $this->setLinvFlag->execute($moduleName, 0);
                    }
                }
            }
        }
    }
}
