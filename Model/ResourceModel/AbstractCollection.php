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
namespace Ves\PageBuilder\Model\ResourceModel;

use Ves\PageBuilder\Model\Enterprise\VersionFeaturesFactory;
/**
 * Abstract collection of PageBuilder
 */
abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    protected $_objectManager;
    /**
     * @var VersionFeaturesFactory
     */
    private $versionFeatures;

    private $_flag_store_filter = false;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        VersionFeaturesFactory $versionFeatures,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
        $this->_objectManager = $objectManager;
        $this->versionFeatures = $versionFeatures;
    }


    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function performAfterLoad($tableName, $columnName)
    {
        $connection = $this->getConnection();
        foreach ($this as $item) {
            $page_stores = array();

            $item->setData("store_id", 0);

            if($item->getData('block_id')) {

              $page_stores = $this->_objectManager->create('Ves\PageBuilder\Model\ResourceModel\Block')->lookupStoreIds($item->getData('block_id'));

            }

            if($alias = $item->getData("alias")) {
              $cms_page = $this->_objectManager->create('Ves\PageBuilder\Model\Block')->loadCMSPage($alias, "identifier", $page_stores);
              if($cms_page->getPageId()) {
                if(!$page_stores) {
                   $page_stores = $cms_page->getStoreId();
                }

                $select = $connection->select()
                            ->from(['cps'=>$this->getTable($tableName)])
                            ->where('cps.'.$columnName.' = (?)', $cms_page->getPageId());

                if ($result = $connection->fetchPairs($select)) {

                    if ($result[$cms_page->getPageId()] == 0) {
                        $stores = $this->storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = $result[$cms_page->getPageId()];
                        $storeCode = $this->storeManager->getStore($storeId)->getCode();
                    }

                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                } elseif($page_stores) {
                    $first_store_id = is_array($page_stores)?(int)$page_stores[0]:$page_stores;
                    $storeCode = "";
                    if($first_store_id){
                        $storeCode = $this->storeManager->getStore($first_store_id)->getCode();
                    }
                    $item->setData('_first_store_id', $first_store_id);
                    $item->setData('store_code', $storeCode);
                }
                $item->setData("store_id", $page_stores);

              }
            }
        }
    }

    /**
     * Add field filter to collection
     *
     * @param array|string $field
     * @param string|int|array|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'store_id') {
            return $this->addStoreFilter($condition, false);
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    abstract public function addStoreFilter($store, $withAdmin = true);

    /**
     * Perform adding filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return void
     */
    protected function performAddStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = [$store->getId()];
        }

        if (!is_array($store)) {
            $store = [$store];
        }

        if ($withAdmin) {
            $store[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        if(!$this->_flag_store_filter) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable('ves_blockbuilder_cms')],
                'main_table.block_id = store_table.block_id',
                []
            )->group(
                'main_table.block_id'
            );
            $this->_flag_store_filter = true;
        }
        $this->addFilter('store', ['in' => $store], 'public');
    }

    /**
     * Join store relation table if there is store filter
     *
     * @param string $tableName
     * @param string $columnName
     * @return void
     */
    protected function joinStoreRelationTable($tableName, $columnName)
    {
        return false;
        if ($this->getFilter('store')) {
            $this->getSelect()->join(
                ['store_table' => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = store_table.' . $columnName,
                []
            )->group(
                'main_table.' . $columnName
            );
        }
        parent::_renderFiltersBefore();
    }

    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }


    public function addFooterFilter() {
      $this->getSelect()
                    ->where('(main_table.block_type IS NULL) OR (main_table.block_type != "page")')
                    ->where("main_table.alias like 'footer%'");

      return $this;
    }
    public function addHeaderFilter() {

      $this->getSelect()
                    ->where('(main_table.block_type IS NULL) OR (main_table.block_type != "page")')
                    ->where("main_table.alias like 'header%'");

      return $this;
    }

    public function loadBuilderWidgets() {
        foreach($this as $item) {
            //Load params and widgets
            if($params = $item->getParams()) {
                $widgets = $this->lookupWidgets($item->getId());
                $data_widgets = [];
                if($widgets) {
                    foreach($widgets as $key => $widget){
                        $data_widgets[$widget['widget_key']] = $widget['widget_shortcode'];
                    }
                }
                $item->setData("widgets", $data_widgets);
            }
        }
        return $this;
    }

    public function lookupWidgets($pageId) {
        $adapter = $this->getConnection();

        $select  = $adapter->select()
                            ->from($this->getTable('ves_blockbuilder_widget'), '*')
                            ->where('block_id = ?',(int)$pageId);

        return $adapter->fetchAll($select);
    }
    public function getVersionFeatures(){
        return $this->versionFeatures;
    }
}
