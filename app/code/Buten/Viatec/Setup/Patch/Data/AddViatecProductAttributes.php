<?php
declare(strict_types=1);

namespace Buten\Viatec\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddViatecProductAttributes implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory          $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }

    public function apply()
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->addAttribute(
            Product::ENTITY,
            'is_viatec',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Is Viatec Product?',
                'input' => 'boolean',
                'class' => '',
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_product_id',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec Product Id',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_product_code',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec Product Code',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_warranty',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec Warranty',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_vendor',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec Vendor',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_category_id',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec Category Id',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_price_uah',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec price UAH',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_price_usd',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec price USD',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_price_uah_vat',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec recommendable price',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );

        $eavSetup->addAttribute(
            Product::ENTITY,
            'viatec_price_uah_novat',
            [
                'type' => 'varchar',
                'backend' => '',
                'frontend' => '',
                'label' => 'Viatec retail price UAH',
                'input' => 'text',
                'class' => '',
                'source' => '',
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => '0',
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => true,
                'unique' => false,
                'group' => 'Viatec',
                'apply_to' => 'simple,virtual'
            ]
        );
    }
}
