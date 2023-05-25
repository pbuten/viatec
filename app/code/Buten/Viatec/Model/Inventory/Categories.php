<?php

namespace Buten\Viatec\Model\Inventory;

use Buten\Viatec\Helper\Data;
use Buten\Viatec\Helper\ViatecConfig;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory as CategoryFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class Categories
{
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $categoryCollectionFactory;

    /**
     * @var CategoryFactory
     */
    protected CategoryFactory $categoryFactory;

    /**
     * @var Category
     */
    protected Category $_category;

    /**
     * @var CategoryRepositoryInterface
     */
    protected CategoryRepositoryInterface $categoryRepository;

    /**
     * @var Data
     */
    protected Data $data;

    /**
     * @param StoreManagerInterface $storeManager
     * @param CollectionFactory $categoryCollectionFactory
     * @param CategoryFactory $categoryFactory
     * @param Category $_category
     * @param CategoryRepositoryInterface $categoryRepository
     * @param Data $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CollectionFactory $categoryCollectionFactory,
        CategoryFactory $categoryFactory,
        Category $_category,
        CategoryRepositoryInterface $categoryRepository,
        Data $data
    ) {
        $this->storeManager = $storeManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->_category = $_category;
        $this->categoryRepository = $categoryRepository;
        $this->data = $data;
    }

    /**
     * @return void
     */
    public function execute($categories)
    {
        if (!$categories) {
            return;
        }
        $count = 0;

        foreach ($categories as $viatecCategory) {
            if ($this->data->getConfigValue(ViatecConfig::REQUIRED_CATEGORIES_CONFIG) != null) {
                $allowedCategories = explode(',', $this->data->getConfigValue(ViatecConfig::REQUIRED_CATEGORIES_CONFIG));
                if (!in_array($viatecCategory['id'], $allowedCategories)) {
                    continue;
                }
            }
            if ($this->categoryExists($viatecCategory['id'])) {
                continue;
            }
            if (!$this->parentCategoryExists($viatecCategory)) {
                continue;
            }
            $this->createCategory($viatecCategory, $this->parentCategoryExists($viatecCategory));
            $count++;
        }
        if ($count > 0) {
            $this->execute($categories);
        }
    }

    /**
     * @param array $viatecCategory
     * @param $parentCategory
     * @return void
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function createCategory($viatecCategory, $parentCategory)
    {
        $category = $this->categoryFactory->create();
        $category->setParentId($parentCategory->getId());
        $categoryCollectionFactory = $this->categoryCollectionFactory->create();
        $cate = $categoryCollectionFactory->addFieldToSelect('*')->addAttributeToFilter('name', $viatecCategory['title'])
            ->getFirstItem();
        if ($cate) {
            $category->setCustomAttribute(
                'url_key',
                $this->_category->formatUrlKey($viatecCategory['title'] . '-' . $viatecCategory['id'])
            );
        }
        $category->setName($viatecCategory['title']);
        $category->setIsActive(true);
        $category->setData('is_viatec_category', 1);
        $category->setData('viatec_category_id', $viatecCategory['id']);
        $category->setData('viatec_parent_id', $viatecCategory['parent_id']);
        $category->setIncludeInMenu(true);
        $this->categoryRepository->save($category);
    }

    /**
     * @param string $viatecCategoryId
     * @return bool
     * @throws LocalizedException
     */
    public function categoryExists($viatecCategoryId)
    {
        $category = $this->categoryCollectionFactory->create();
        $cate = $category->addFieldToSelect('viatec_category_id')
            ->addAttributeToFilter('viatec_category_id', $viatecCategoryId)
            ->getFirstItem();
        if (!$cate->getId()) {
            return false;
        }
        return true;
    }

    /**
     * @param $viatecCategory
     * @return false|CategoryInterface|DataObject
     * @throws LocalizedException
     */
    public function parentCategoryExists($viatecCategory)
    {
        if ($viatecCategory['parent_id'] == 0) {
            $cate = $this->categoryFactory->create()->load($this->data->getConfigValue(ViatecConfig::ROOT_CATEGORY_ID_CONFIG));
            return $cate;
        }
        $category = $this->categoryCollectionFactory->create();
        $cate = $category->addFieldToSelect('*')->addAttributeToFilter('viatec_parent_id', $viatecCategory['parent_id'])
            ->getFirstItem();
        if (!$cate->getId()) {
            $cate = $this->categoryFactory->create()->load($this->data->getConfigValue(ViatecConfig::ROOT_CATEGORY_ID_CONFIG));
            return $cate;
        }
        return $cate;
    }
}
