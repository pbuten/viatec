<?php

namespace Buten\Viatec\Helper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Api\AttributeOptionManagementInterface;
use Magento\Eav\Api\Data\AttributeOptionInterfaceFactory;
use Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory;
use Magento\Eav\Model\Entity\Attribute\OptionLabel;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Eav\Model\Entity\Attribute\Source\TableFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;

class AttributeOptions extends AbstractHelper
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected ProductAttributeRepositoryInterface $attributeRepository;

    /**
     * @var array
     */
    protected $attributeValues;

    /**
     * @var TableFactory
     */
    protected TableFactory $tableFactory;

    /**
     * @var AttributeOptionManagementInterface
     */
    protected AttributeOptionManagementInterface $attributeOptionManagement;

    /**
     * @var AttributeOptionLabelInterfaceFactory
     */
    protected AttributeOptionLabelInterfaceFactory $optionLabelFactory;

    /**
     * @var AttributeOptionInterfaceFactory
     */
    protected AttributeOptionInterfaceFactory $optionFactory;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param TableFactory $tableFactory
     * @param AttributeOptionManagementInterface $attributeOptionManagement
     * @param AttributeOptionLabelInterfaceFactory $optionLabelFactory
     * @param AttributeOptionInterfaceFactory $optionFactory
     */
    public function __construct(
        Context $context,
        ProductAttributeRepositoryInterface $attributeRepository,
        TableFactory $tableFactory,
        AttributeOptionManagementInterface $attributeOptionManagement,
        AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        AttributeOptionInterfaceFactory $optionFactory
    ) {
        parent::__construct($context);

        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
    }

    /**
     * Get attribute by code.
     *
     * @param string $attributeCode
     * @return ProductAttributeInterface
     */
    public function getAttribute($attributeCode)
    {
        return $this->attributeRepository->get($attributeCode);
    }

    /**
     * Find or create a matching attribute option
     *
     * @param string $attributeCode Attribute the option should exist in
     * @param string $label Label to find or add
     * @return int
     * @throws LocalizedException
     */
    public function createOrGetId($attributeCode, $label)
    {
        if (strlen($label) < 1) {
            throw new LocalizedException(
                __('Label for %1 must not be empty.', $attributeCode)
            );
        }

        // Does it already exist?
        $optionId = $this->getOptionId($attributeCode, $label);

        if (!$optionId) {
            // If no, add it.

            /** @var OptionLabel $optionLabel */
            $optionLabel = $this->optionLabelFactory->create();
            $optionLabel->setStoreId(0);
            $optionLabel->setLabel($label);

            $option = $this->optionFactory->create();
            $option->setLabel($optionLabel->getLabel());
            $option->setStoreLabels([$optionLabel]);
            $option->setSortOrder(0);
            $option->setIsDefault(false);

            $this->attributeOptionManagement->add(
                \Magento\Catalog\Model\Product::ENTITY,
                $this->getAttribute($attributeCode)->getAttributeId(),
                $option
            );

            // Get the inserted ID. Should be returned from the installer, but it isn't.
            $optionId = $this->getOptionId($attributeCode, $label, true);
        }

        return $optionId;
    }

    /**
     * Find the ID of an option matching $label, if any.
     *
     * @param string $attributeCode Attribute code
     * @param string $label Label to find
     * @param bool $force If true, will fetch the options even if they're already cached.
     * @return int|false
     */
    public function getOptionId($attributeCode, $label, $force = false)
    {
        /** @var Attribute $attribute */
        $attribute = $this->getAttribute($attributeCode);

		if ($attribute->getSource()->getOptionId($label) !== null) {
			return $attribute->getSource()->getOptionId($label);
		}

        return false;
    }
}
