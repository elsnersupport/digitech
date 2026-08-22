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
namespace Appseconnect\Product\Model;

use Appseconnect\Product\Api\ProductUpdateManagementInterface as ProductApiInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\Framework\Exception\StateException;
/**
 * @since 101.0.0
 *
 * @api
 */
class ProductUpdateManagement implements ProductApiInterface {

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    private $resultJsonFactory;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param JsonFactory $resultJsonFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public $productRepository;
    public $logger;
    public $stockRegistry;
    public $stockItemRepository;
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Updates the specified product from the request payload.
     *
     * @param mixed $products
     * @return mixed
     */
    public function updateProduct($products)
    {
        $returnData=array();
        if (!empty($products)) {
        	$error = false;
			foreach ($products as $product) {
				foreach ($product as $pro) {
					try {
                        $json_data = array();
						$sku = $pro['sku'];
                        $json_data['sku'] = $sku;
						$productObject = $this->productRepository->get($sku);
						if(!empty($pro['price'])) {
                            $productObject->setPrice($pro['price']);
                            $json_data['price'] = $pro['price'];
                        }
                        if(!empty($pro['name'])) {
                            $productObject->setName($pro['name']);
                        }
                        if(!empty($pro['status'])) {
                            $productObject->setStatus($pro['status']);
                        }
                        if(!empty($pro['visibility'])) {
                            $productObject->setVisibility($pro['visibility']);
                        }
                        if(!empty($pro['description'])) {
                            $productObject->setDescription($pro['description']);
                        }
                        if(!empty($pro['short_description'])) {
                            $productObject->setShortDescription($pro['short_description']);
                        }
                        if(!empty($pro['type_id'])) {
                            $productObject->setTypeId($pro['type_id']);
                        }
                        if(!empty($pro['weight'])) {
                            $productObject->setWeight($pro['weight']);
                        }
                        if(!empty($pro['custom_attributes'])) {
                            foreach($pro['custom_attributes'] as $key => $value) {
                                if(is_array($value['value'])) {
                                    $data = implode(',',$value['value']);
                                    $productObject->setData($value['attribute_code'],$data);
                                } else {
                                    $productObject->setData($value['attribute_code'],$value['value']);
                                }
                            }
                        }

                        try {
							$this->productRepository->save($productObject);

                            array_push($returnData,$pro);
                           // return  $resultJson->setData($json_data);

						} catch (\Exception $e) {
								throw new StateException(__('Cannot save product.'));
						}

                        if(!empty($pro['stock_item']) && isset($pro['stock_item']['product_id'])) {
                            $productObject = $this->productRepository->get($sku);
                            $productId = $productObject->getEntityId();
                            $websiteId = isset($pro['stock_item']['websiteId']) ?: null;
                            $origStockItem = $this->stockRegistry->getStockItem($productId, $websiteId);
                            $data = $pro['stock_item'];
                            if ($origStockItem->getItemId()) {
                                unset($data['item_id']);
                            }
                            $origStockItem->addData($data);
                            $origStockItem->setProductId($productId);
                            $this->stockItemRepository->save($origStockItem)->getItemId();
                        }
					} catch (\Magento\Framework\Exception\LocalizedException $e) {
							$messages[] = " SKU='" . $pro['sku'] ."' | " . $e->getMessage();
							$error = true;
					}
				}
			}
            if ($error) {
	            $this->writeLog(implode(" || ",$messages));
	            return $messages;
	        }
        }
        return  $returnData;
        //return  $resultJson->setData($returnData);
    }


    /**
     * @param $log
     * @return void
     */
    public function writeLog($log)
    {
        $this->logger->info($log);
    }
}
