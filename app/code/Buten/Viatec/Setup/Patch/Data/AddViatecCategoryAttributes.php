<?php
declare(strict_types=1);

namespace Buten\Viatec\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddViatecCategoryAttributes implements DataPatchInterface, PatchRevertableInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->addAttributeGroup(
            \Magento\Catalog\Model\Category::ENTITY,
            $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Category::ENTITY),
            'Viatec',
            99
        );
        $eavSetup->addAttributeGroup(
            \Magento\Catalog\Model\Category::ENTITY,
            $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Category::ENTITY),
            'Brain',
            99
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'is_viatec_category',
            [
                'type' => 'int',
                'label' => 'Is Viatec Category',
                'input' => 'select',
                'source' => Boolean::class,
                'sort_order' => 432,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'group' => 'Viatec',
                'visible' => true,
                'required' => false,
                'default' => 0
            ],
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'viatec_category_id',
            [
                'type' => 'int',
                'label' => 'Viatec Category Id',
                'input' => 'text',
                'sort_order' => 433,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'default' => null,
                'group' => 'Viatec'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'viatec_parent_id',
            [
                'type' => 'int',
                'label' => 'Viatec Parent Category Id',
                'input' => 'text',
                'sort_order' => 434,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'default' => null,
                'group' => 'Viatec'
            ]
        );
    }

    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'is_viatec_category');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'viatec_category_id');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'viatec_parent_id');

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [

        ];
    }
}
