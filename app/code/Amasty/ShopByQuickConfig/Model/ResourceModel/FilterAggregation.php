<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
 * @package ILN Quick Config for Magento 2 (System)
 */

namespace Amasty\ShopByQuickConfig\Model\ResourceModel;

use Amasty\Shopby\Helper\Category;
use Amasty\ShopByQuickConfig\Model\FilterData;
use Amasty\ShopByQuickConfig\Model\Source\InputType;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Framework\DataObject;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;

class FilterAggregation extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const MAIN_TABLE = 'amasty_shopbyquick_filter_tmp';

    /**
     * Temporary table column names.
     */
    public const ID = 'id';

    public const IS_FILTERABLE = 'is_filterable';

    public const IS_CUSTOM_FILTER = 'is_custom_filter';

    public const ATTRIBUTE_ID = 'attribute_id';

    public const ATTRIBUTE_CODE = 'attribute_code';

    public const FRONTEND_INPUT = 'frontend_input';

    public const LABEL = 'label';

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::ID);
    }

    /**
     * Create temporary table for filters
     */
    public function createTable(): void
    {
        $connection = $this->getConnection();
        $tableName = $this->getMainTable();
        $table = $connection->newTable($tableName);
        $table->addColumn(
            self::ID,
            Table::TYPE_INTEGER,
            null,
            [
                'identity' => true,
                'unsigned' => true,
                'nullable' => false,
                'primary' => true
            ],
        );
        $table->addColumn(
            self::ATTRIBUTE_ID,
            Table::TYPE_INTEGER,
            null,
            [
                'unsigned' => true,
                'nullable' => false
            ],
        );
        $table->addColumn(
            self::ATTRIBUTE_CODE,
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
                'default' => ''
            ]
        );
        $table->addColumn(
            self::FRONTEND_INPUT,
            Table::TYPE_TEXT,
            50,
            [
                'nullable' => true,
                'default' => null
            ]
        );
        $table->addColumn(
            self::LABEL,
            Table::TYPE_TEXT,
            255,
            [
                'nullable' => false,
                'default' => ''
            ]
        );
        $table->addColumn(
            self::IS_FILTERABLE,
            Table::TYPE_SMALLINT,
            5,
            [
                'unsigned' => true,
                'nullable' => false,
                'default' => 0
            ]
        );
        $table->addColumn(
            self::IS_CUSTOM_FILTER,
            Table::TYPE_BOOLEAN,
            null,
            [
                'nullable' => false,
                'default' => false
            ]
        );
        $connection->createTemporaryTable($table);
    }

    /**
     * Insert filter attributes to tmp table.
     *
     * @param Select $select
     */
    public function insertAttributesSelect(Select $select): void
    {
        $select->reset(Select::COLUMNS);
        $select->columns(
            [
                AttributeInterface::ATTRIBUTE_ID,
                AttributeInterface::ATTRIBUTE_CODE,
                AttributeInterface::FRONTEND_INPUT => $this->getInputTypeExpression(),
                self::LABEL => AttributeInterface::FRONTEND_LABEL
            ]
        );
        $select->columns([EavAttributeInterface::IS_FILTERABLE], 'additional_table');
        $adapter = $this->getConnection();
        $query = $adapter->insertFromSelect(
            $select,
            $this->getMainTable(),
            [self::ATTRIBUTE_ID, self::ATTRIBUTE_CODE, self::FRONTEND_INPUT, self::LABEL, self::IS_FILTERABLE]
        );

        $adapter->query($query);
    }

    /**
     * Prepare input type expression with swatches types.
     *
     * @return \Zend_Db_Expr
     */
    private function getInputTypeExpression(): \Zend_Db_Expr
    {
        $adapter = $this->getConnection();
        $swatchesExpression = 'JSON_UNQUOTE(JSON_EXTRACT(additional_table.additional_data, \'$.swatch_input_type\'))';

        return $adapter->getCheckSql(
            'JSON_CONTAINS_PATH(additional_table.additional_data, \'one\', \'$.swatch_input_type\')',
            $adapter->getConcatSql(["'swatch_'", $swatchesExpression]),
            AttributeInterface::FRONTEND_INPUT
        );
    }

    /**
     * Insert custom filter data to tmp table.
     *
     * @param FilterData|DataObject $object
     */
    public function insertCustomFilter(DataObject $object): void
    {
        $table = $this->getMainTable();
        $bind = $this->_prepareDataForTable($object, $table);
        $bind[self::ATTRIBUTE_CODE] = $object->getFilterCode();
        $bind[self::IS_CUSTOM_FILTER] = true;
        $bind[self::IS_FILTERABLE] = (int) $object->getIsEnabled();
        $bind[self::FRONTEND_INPUT] = InputType::CUSTOM_TYPE;
        
        $this->getConnection()->insert($table, $bind);
    }
}
