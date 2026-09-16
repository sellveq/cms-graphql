<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright © 2018 Scandiweb, Ltd (https://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\CmsGraphQl\Model\Resolver\DataProvider;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Block as CoreBlock;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use Magento\Widget\Model\Template\FilterEmulate;

class Block extends CoreBlock
{
    /**
     * @param BlockRepositoryInterface $blockRepository
     * @param FilterEmulate $widgetFilter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        private readonly BlockRepositoryInterface $blockRepository,
        private readonly FilterEmulate $widgetFilter,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($blockRepository, $widgetFilter, $searchCriteriaBuilder);
    }

    /**
     * get block data by identifier, returning `disabled: true` rather than throwing for inactive blocks
     * @param string $blockIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getBlockByIdentifier(string $blockIdentifier, int $storeId): array
    {
        return $this->fetchBlockData($blockIdentifier, BlockInterface::IDENTIFIER, $storeId);
    }

    /**
     * core routes a numeric identifier here, so it answers the same shape rather than core's
     * @param int $blockId
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getBlockById(int $blockId, int $storeId): array
    {
        return $this->fetchBlockData($blockId, BlockInterface::BLOCK_ID, $storeId);
    }

    /**
     * the one lookup both entry points share
     * @param mixed $identifier
     * @param string $field
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function fetchBlockData($identifier, string $field, int $storeId): array
    {
        // no is_active filter: reporting `disabled` is this override's purpose
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter($field, $identifier)
            ->addFilter(Store::STORE_ID, [$storeId, Store::DEFAULT_STORE_ID], 'in')
            ->create();

        $blockResults = $this->blockRepository->getList($searchCriteria)->getItems();

        if (empty($blockResults)) {
            throw new NoSuchEntityException(
                __('The CMS block with the "%1" ID doesn\'t exist.', $identifier)
            );
        }

        $block = current($blockResults);

        // block_id is not a schema field, but Block\Identity gates every cache tag on its presence
        if (!$block->isActive()) {
            return [
                BlockInterface::BLOCK_ID => $block->getId(),
                BlockInterface::IDENTIFIER => $block->getIdentifier(),
                'disabled' => true
            ];
        }

        $renderedContent = $this->widgetFilter->filter($block->getContent());

        return [
            BlockInterface::BLOCK_ID => $block->getId(),
            BlockInterface::IDENTIFIER => $block->getIdentifier(),
            BlockInterface::TITLE => $block->getTitle(),
            BlockInterface::CONTENT => $renderedContent,
            'disabled' => false
        ];
    }
}
