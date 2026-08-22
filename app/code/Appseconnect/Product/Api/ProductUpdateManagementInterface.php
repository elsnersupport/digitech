<?php
/**
 * Namespace
 *
 * @category API
 * @package  Appseconnect
 * @author   Insync Magento Team <contact@insync.co.in>
 * @license  Insync https://insync.co.in
 * @link     https://www.appseconnect.com/
 */

namespace Appseconnect\Product\Api;

/**
 * @since 101.0.0
 *
 * @api
 */
interface ProductUpdateManagementInterface
{
    /**
     * Updates the specified products in item array.
     *
     * @param mixed $products
     * @return mixed
     */
    public function updateProduct($products);
}
