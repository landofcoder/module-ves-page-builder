<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_PageBuilder
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\PageBuilder\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Company\Model\CompanyFactory;
use Magento\Company\Model\ResourceModel\Company\CollectionFactory;
use Magento\Company\Model\CompanyRepository;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface {

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;
    /**
	 * @param EavSetupFactory $eavSetupFactory 
	 */
	public function __construct(
		EavSetupFactory $eavSetupFactory
		)
	{
		$this->eavSetupFactory = $eavSetupFactory;
	}
    public function upgrade( ModuleDataSetupInterface $setup, ModuleContextInterface $context )
    {
        if ( version_compare( $context->getVersion(), '1.0.2', '<' ) ) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $data = array(
                'group' => 'General',
                'type' => 'varchar',
                'input' => 'select',
                'label' => 'Element Builder for Description',
                'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                'frontend' => '',
                'source' => 'Ves\PageBuilder\Model\Config\Source\ElementList',
                'visible' => 1,
                'required' => 0,
                'user_defined' => 1,
                'used_for_price_rules' => 1,
                'position' => 2,
                'unique' => 0,
                'default' => '',
                'sort_order' => 100,
                'is_global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'is_required' => 0,
                'is_configurable' => 1,
                'is_searchable' => 0,
                'is_visible_in_advanced_search' => 0,
                'is_comparable' => 0,
                'is_filterable' => 0,
                'is_filterable_in_search' => 1,
                'is_used_for_promo_rules' => 1,
                'is_html_allowed_on_front' => 0,
                'is_visible_on_front' => 1,
                'used_in_product_listing' => 1,
                'used_for_sort_by' => 0,
                );
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'element_builder',
                $data);
        }
    }
}
