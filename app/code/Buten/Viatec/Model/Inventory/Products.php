<?php

namespace Buten\Viatec\Model\Inventory;

use Buten\Viatec\Helper\AddProductAttribute;
use Buten\Viatec\Helper\AttributeOptions;
use Buten\Viatec\Helper\Data;
use Buten\Viatec\Helper\ViatecConfig;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory as AttributeSetCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem\Io\File;
use Magento\InventoryCatalogApi\Model\SourceItemsProcessorInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Psr\Log\LoggerInterface;

class Products
{
    /**
     * @var Data
     */
    protected Data $data;

    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productInterfaceFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $categoryCollectionFactory;

    /**
     * @var DirectoryList
     */
    protected DirectoryList $directoryList;

    /**
     * @var File
     */
    protected File $file;

    /**
     * @var CategoryRepository
     */
    protected CategoryRepository $categoryRepository;

    /**
     * @var SourceItemsProcessorInterface
     */
    protected SourceItemsProcessorInterface $sourceItemsProcessor;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLinkManagementInterface;

    /**
     * @var AttributeSetCollectionFactory
     */
    protected AttributeSetCollectionFactory $attributeSetcollectionFactory;

    /**
     * @var AttributeRepositoryInterface
     */
    protected AttributeRepositoryInterface $attributeRepository;

    /**
     * @var AddProductAttribute
     */
    protected AddProductAttribute $addProductAttribute;

    /**
     * @var AttributeOptions
     */
    protected AttributeOptions $attributeOptions;

    /**
     * @var SourceItemInterfaceFactory
     */
    protected SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * @var SourceItemsSaveInterface
     */
    protected SourceItemsSaveInterface $sourceItemsSaveInterface;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @param CollectionFactory $categoryCollectionFactory
     * @param Data $data
     * @param ProductInterfaceFactory $productInterfaceFactory
     * @param ProductRepositoryInterface $productRepository
     * @param DirectoryList $directoryList
     * @param File $file
     * @param CategoryRepository $categoryRepository
     * @param SourceItemsProcessorInterface $sourceItemsProcessor
     * @param CategoryLinkManagementInterface $categoryLinkManagementInterface
     * @param AttributeSetCollectionFactory $attributeSetcollectionFactory
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AddProductAttribute $addProductAttribute
     * @param AttributeOptions $attributeOptions
     * @param SourceItemInterfaceFactory $sourceItemFactory
     * @param SourceItemsSaveInterface $sourceItemsSaveInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactory               $categoryCollectionFactory,
        Data                            $data,
        ProductInterfaceFactory         $productInterfaceFactory,
        ProductRepositoryInterface      $productRepository,
        DirectoryList                   $directoryList,
        File                            $file,
        CategoryRepository              $categoryRepository,
        SourceItemsProcessorInterface   $sourceItemsProcessor,
        CategoryLinkManagementInterface $categoryLinkManagementInterface,
        AttributeSetCollectionFactory   $attributeSetcollectionFactory,
        AttributeRepositoryInterface    $attributeRepository,
        AddProductAttribute             $addProductAttribute,
        AttributeOptions                $attributeOptions,
        SourceItemInterfaceFactory      $sourceItemFactory,
        SourceItemsSaveInterface        $sourceItemsSaveInterface,
        LoggerInterface                 $logger
    )
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->data = $data;
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepository = $productRepository;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->categoryRepository = $categoryRepository;
        $this->sourceItemsProcessor = $sourceItemsProcessor;
        $this->categoryLinkManagementInterface = $categoryLinkManagementInterface;
        $this->attributeSetcollectionFactory = $attributeSetcollectionFactory;
        $this->attributeRepository = $attributeRepository;
        $this->addProductAttribute = $addProductAttribute;
        $this->attributeOptions = $attributeOptions;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->logger = $logger;
    }

    /**
     * @param array $viatecProducts
     * @param string $sid
     * @return void
     */
    public function saveProducts(array $viatecProducts)
    {
        $allowedCategories = false;
        if ($this->data->getConfigValue(ViatecConfig::REQUIRED_CATEGORIES_CONFIG) != null) {
            $allowedCategories = explode(',', $this->data->getConfigValue(ViatecConfig::REQUIRED_CATEGORIES_CONFIG));
        }
        foreach ($viatecProducts as $viatecProduct) {
            if (!$viatecProduct['code']) {
                continue;
            }
            if (!$viatecProduct['price_uah_vat'] && !$viatecProduct['price_uah_novat']) {
                continue;
            }
            if ($allowedCategories) {
                if (!in_array($viatecProduct['category_id'], $allowedCategories)) {
                    continue;
                }
            }
            try {
                $product = $this->productRepository->get($viatecProduct['code']);
                $this->updateProduct($viatecProduct, $product);
                continue;
            } catch (NoSuchEntityException) {
                $this->saveProduct($viatecProduct);
            }
        }
    }

    /**
     * @param $viatecProduct
     * @return void
     */
    public function saveProduct($viatecProduct)
    {
        if ($viatecProduct['stock'] == 'no') {
            return;
        }
        $product = $this->productInterfaceFactory->create();

        $product->setSku($viatecProduct['code']);
        $name = $viatecProduct['model'] . ' ' . $viatecProduct['title'] . ' (' . $viatecProduct['code'] . ')';
        $product->setName($name);
        $product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $product->setVisibility(4);
        if ($viatecProduct['price_uah_vat']) {
            $product->setPrice($viatecProduct['price_uah_vat']);
        } else {
            $product->setPrice($viatecProduct['price_uah_novat']);
        }
        $product->setWebsiteIds([1]);

        $this->setCustomAttributes($product, $viatecProduct);

        $this->setImages($product, $viatecProduct);
        $product->setAttributeSetId($this->getViatecAttributeSetId());
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        try {
            $newProduct = $this->productRepository->save($product);
            $category = $this->categoryCollectionFactory->create();
            $cate = $category->addFieldToSelect('*')
                ->addAttributeToFilter('viatec_category_id', $viatecProduct['category_id'])
                ->getFirstItem();
            if ($cate->getId()) {
                $this->assignProductToCategory($newProduct, [$cate->getId()]);
            }

            if ($viatecProduct['brand']) {
                $optionId = $this->attributeOptions->createOrGetId('manufacturer', $viatecProduct['brand']);
                $product->setCustomAttribute('manufacturer', $optionId);
            } else {
                $optionId = $this->attributeOptions->createOrGetId('manufacturer', 'Не вказано');
                $product->setCustomAttribute('manufacturer', $optionId);
            }
            if ($viatecProduct['properties']) {
                $this->addAttribute($viatecProduct['properties'], $newProduct);
            }
            $this->productRepository->save($product);

            if (isset($viatecProduct['stock'])) {
                if ($viatecProduct['stock'] == 'few') {
                    $this->updateStock($newProduct, 2);
                } elseif ($viatecProduct['stock'] == 'yes') {
                    $this->updateStock($newProduct, 10);
                }
            }
            $this->data->setConfigValue(
                ViatecConfig::NEW_PRODUCTS_CONFIG,
                $this->data->getConfigValue(ViatecConfig::NEW_PRODUCTS_CONFIG) . ' ' . $newProduct->getSku()
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage(), $e->getTrace());
            return;
        }
    }

    /**
     * @param $product
     * @param $viatecProduct
     * @return void
     */
    public function setCustomAttributes($product, $viatecProduct)
    {
        $description = '';
        if (isset($viatecProduct['properties']['property'])) {
            foreach ($viatecProduct['properties']['property'] as $property) {
                if (isset($property['name'])) {
                    $description .= '<strong>' . $property['name'] . ': ' . '</strong>' . $property['value'] . '<br>';
                }
            }
        }
        if ($description) {
            $description = '<p>' . $description . '</p>';
        }
        $viatecProductId = '';
        $viatecProductCode = '';
        $viatecProductWarranty = '';
        $viatecProductBrand = '';
        $viatecProductCategoryId = '';
        $viatecProductPriceUah = '';
        $viatecProductPrice = '';
        $viatecProductPriceUahVat = '';
        $viatecProductPriceUahNovat = '';
        $viatecProductDescr = '';
        if (isset($viatecProduct['id'])) {
            $viatecProductId = $viatecProduct['id'];
        }
        if (isset($viatecProduct['code'])) {
            $viatecProductCode = $viatecProduct['code'];
        }
        if (isset($viatecProduct['warranty'])) {
            $viatecProductWarranty = $viatecProduct['warranty'];
        }
        if (isset($viatecProduct['brand'])) {
            $viatecProductBrand = $viatecProduct['brand'];
        }
        if (isset($viatecProduct['category_id'])) {
            $viatecProductCategoryId = $viatecProduct['category_id'];
        }
        if (isset($viatecProduct['price_uah'])) {
            $viatecProductPriceUah = $viatecProduct['price_uah'];
        }
        if (isset($viatecProduct['price_uah_vat'])) {
            $viatecProductPrice = $viatecProduct['price'];
        }
        if (isset($viatecProduct['id'])) {
            $viatecProductPriceUahVat = $viatecProduct['price_uah_vat'];
        }
        if (isset($viatecProduct['price_uah_novat'])) {
            $viatecProductPriceUahNovat = $viatecProduct['price_uah_novat'];
        }
        if (isset($viatecProduct['descr'])) {
            $viatecProductDescr = '<p>' . $viatecProduct['descr'] . '</p>';
        }
        $product->setCustomAttributes([
            'is_viatec' => '1',
            'viatec_product_id' => $viatecProductId,
            'viatec_product_code' => $viatecProductCode,
            'viatec_warranty' => $viatecProductWarranty,
            'viatec_vendor' => $viatecProductBrand,
            'viatec_category_id' => $viatecProductCategoryId,
            'viatec_price_uah' => $viatecProductPriceUah,
            'viatec_price_usd' => $viatecProductPrice,
            'viatec_price_uah_vat' => $viatecProductPriceUahVat,
            'viatec_price_uah_novat' => $viatecProductPriceUahNovat,
            'short_description' => $viatecProductDescr,
            'description' => $description
        ]);
    }

    /**
     * @param Product $product
     * @param array $viatecProduct
     * @return void
     */
    public function updateProduct($viatecProduct, $product)
    {
        if ($viatecProduct['price_uah_vat']) {
            $product->setPrice($viatecProduct['price_uah_vat']);
        } elseif ($viatecProduct['price_uah_novat']) {
            $product->setPrice($viatecProduct['price_uah_novat']);
        }
        $this->setCustomAttributes($product, $viatecProduct);

        if (!$product->getCategoryIds()) {
            $category = $this->categoryCollectionFactory->create();
            $cate = $category->addFieldToSelect('*')
                ->addAttributeToFilter('viatec_category_id', $viatecProduct['category_id'])
                ->getFirstItem();
            if ($cate->getId()) {
                $this->assignProductToCategory($product, [$cate->getId()]);
            }
        }
        if (count($product->getMediaGalleryImages()->getItems()) < 1) {
            $this->setImages($product, $viatecProduct);
        }
        try {
            $this->productRepository->save($product);
            if ($viatecProduct['stock'] == 'no') {
                $this->updateStock($product, 0);
            } elseif ($viatecProduct['stock'] == 'few') {
                $this->updateStock($product, 2);
            } elseif ($viatecProduct['stock'] == 'yes') {
                $this->updateStock($product, 10);
            }
            if ($viatecProduct['properties']) {
                $this->addAttribute($viatecProduct['properties'], $product);
                $this->productRepository->save($product);
            }
            if (!$viatecProduct['price_uah_vat'] && !$viatecProduct['price_uah_novat']) {
                $this->updateStock($product, 0);
            }
        } catch (CouldNotSaveException|InputException|StateException $e) {
        }
    }

    /**
     * @param $product
     * @param $viatecProduct
     * @return void
     */
    public function setImages($product, $viatecProduct)
    {
        try {
            $image = $this->getImage($product, $viatecProduct);
            $mediaAttribute = ['image', 'small_image', 'thumbnail'];
            $product->addImageToMediaGallery($image, $mediaAttribute, true, false);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage() . ' ' . $exception->getFile() . ' ' . $exception->getLine(), $exception->getTrace());
        }
    }

    /**
     * @param $imageUrl
     * @return false|string
     * @throws LocalizedException
     */
    public function getImage($product, $viatecProduct)
    {
        /** @var string $tmpDir */
        $tmpDir = $this->getMediaDirTmpDir();
        $this->file->checkAndCreateFolder($tmpDir);
        $imageUrl = $viatecProduct['image'];
        $explodedOldName = explode('.', baseName($imageUrl));
        $tmpFileName = $product->formatUrlKey($viatecProduct['code']) . '.' . $explodedOldName[array_key_last($explodedOldName)];
        $newFileName = $tmpDir . $tmpFileName;
        $result = $this->file->read($imageUrl, $newFileName);
        if (!$result) {
            return false;
        }
        return $newFileName;
    }

    /**
     * Media directory name for the temporary file storage
     * pub/media/tmp
     *
     * @return string
     */
    protected function getMediaDirTmpDir()
    {
        return $this->directoryList->getPath(DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'tmp/';
    }

    /**
     * @param Product|ProductInterface $product
     * @param $qty
     * @return void
     * @throws InputException
     */
    public function updateStock($product, $qty)
    {
        $sourceItem = $this->sourceItemFactory->create();
        $sourceItem->setSourceCode('default');
        $sourceItem->setSku($product->getSku());
        $sourceItem->setQuantity($qty);
        $sourceItem->setStatus(1);
        $this->sourceItemsSaveInterface->execute([$sourceItem]);
    }

    /**
     * Assign product to category.
     *
     * @param Product|ProductInterface $product
     * @param array $categoryIds
     *
     * @return void
     */
    public function assignProductToCategory($product, $categoryIds = [])
    {
        if (!empty($categoryIds)) {
            $this->categoryLinkManagementInterface->assignProductToCategories(
                $product->getSku(),
                $categoryIds
            );
        }
    }

    /**
     * @return mixed
     */
    public function getViatecAttributeSetId()
    {
        $attributeSetCollectionFactory = $this->attributeSetcollectionFactory->create();
        $viatecAttributeSet = $attributeSetCollectionFactory->addFieldToSelect('attribute_set_id')
            ->addFieldToFilter('attribute_set_name', 'Viatec')
            ->getFirstItem();
        return $viatecAttributeSet->getAttributeSetId();
    }

    /**
     * @param ProductInterface $product
     * @param array $viatecProductOptions
     * @return void
     */
    public function addAttribute(array $viatecProductOptions, ProductInterface $product)
    {
        foreach ($viatecProductOptions as $option) {
            if (!isset($option['name'])) {
                continue;
            }
            $attributeCode = 'viatec_option_' . preg_replace("/[^a-zA-Z0-9]+/", "", $product->formatUrlKey($option['name']));
            try {
                $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
                $optionId = $this->attributeOptions->createOrGetId($attributeCode, $option['value']);
                $product->setCustomAttribute($attributeCode, $optionId);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->addProductAttribute->createAttribute($attributeCode, $option['name']);
            }
        }
    }
}
