<?php

namespace Buten\Viatec\Model\Inventory;

use Buten\Viatec\Helper\AddProductAttribute;
use Buten\Viatec\Helper\AttributeOptions;
use Buten\Viatec\Helper\Data;
use Buten\Viatec\Helper\ViatecConfig;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;

class Attributes
{
    protected Products $products;
    protected Data $data;
    protected Product $product;

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
     * Constructor
     *
     * @param Products $products
     * @param Data $data
     * @param Product $product
     * @param AttributeRepositoryInterface $attributeRepository
     * @param AddProductAttribute $addProductAttribute
     * @param AttributeOptions $attributeOptions
     */
    public function __construct(
        Products        $products,
        Data            $data,
        Product $product,
        AttributeRepositoryInterface $attributeRepository,
        AddProductAttribute $addProductAttribute,
        AttributeOptions $attributeOptions
    ) {
        $this->products = $products;
        $this->data = $data;
        $this->product = $product;
        $this->attributeRepository = $attributeRepository;
        $this->addProductAttribute = $addProductAttribute;
        $this->attributeOptions = $attributeOptions;
    }

    public function updateAttributes(array $viatecProducts)
    {
        $allowedCategories = [];
        if ($this->data->getConfigValue(ViatecConfig::REQUIRED_CATEGORIES_CONFIG) != null) {
            $allowedCategories = explode(',', $this->data->getConfigValue(ViatecConfig::REQUIRED_CATEGORIES_CONFIG));
        }
        $viatecProductOptions = [];
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
            if (!$viatecProduct['properties']) {
                continue;
            }
            foreach ($viatecProduct['properties'] as $property) {
                if (isset($property['name'])) {
                    if (!isset($viatecProductOptions[$property['name']])) {
                        $viatecProductOptions[$property['name']][] = $property['value'];
                    } else {
                        if (!in_array($property['value'], $viatecProductOptions[$property['name']])) {
                            $viatecProductOptions[$property['name']][] = $property['value'];
                        }
                    }
                }
            }
        }

        $this->addAttribute($viatecProductOptions);
    }

    /**
     * @param array $viatecProductOptions
     * @return void
     */
    public function addAttribute(array $viatecProductOptions)
    {
        foreach ($viatecProductOptions as $key => $option) {
            $attributeCode = 'viatec_option_' . preg_replace("/[^a-zA-Z0-9]+/", "", $this->product->formatUrlKey($key));
            try {
                $this->attributeRepository->get(Product::ENTITY, $attributeCode);
                $this->addAtributeOptions($attributeCode, $option);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->addProductAttribute->createAttribute($attributeCode, $key);
                $this->addAtributeOptions($attributeCode, $option);
            }
        }
    }

    public function addAtributeOptions($attributeCode, $values)
    {
        try {
            $this->attributeRepository->get(Product::ENTITY, $attributeCode);
            foreach ($values as $value) {
                $this->attributeOptions->createOrGetId($attributeCode, $value);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        }
    }
}
