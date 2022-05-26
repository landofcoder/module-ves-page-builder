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
namespace Ves\PageBuilder\Model\Config\Source;

class ElementList extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var \Ves\PageBuilder\Model\BlockFactory
     */
    protected  $_blockFactory;

    /**
     *
     * @param \Ves\PageBuilder\Model\BlockFactory $blockFactory
     */
    public function __construct(
        \Ves\PageBuilder\Model\BlockFactory $blockFactory
    ) {
        $this->_blockFactory = $blockFactory;
    }


    /**
     * Get Gift Card available templates
     *
     * @return array
     */
    public function getAvailableTemplate()
    {
        $blocks = $this->_blockFactory->create()->getCollection()
                                    ->addFieldToFilter('status', 1);
        $listBlocks = array();
        foreach ($blocks as $block) {
            $listBlocks[] = array('label' => $block->getTitle(),
                'value' => $block->getId());
        }
        return $listBlocks;
    }

    /**
     * Get model option as array
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        $options = array();
        $options = $this->getAvailableTemplate();

        if ($withEmpty) {
            array_unshift($options, array(
                'value' => '',
                'label' => '-- Please Select --',
                ));
        }
        return $options;
    }
}
