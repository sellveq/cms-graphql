<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright © 2018 Scandiweb, Ltd (https://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace ScandiPWA\CmsGraphQl\Model\Resolver\DataProvider;

use Magento\Cms\Api\Data\PageInterface as OriginalPageInterface;
use Magento\Cms\Api\GetPageByIdentifierInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\CmsGraphQl\Model\Resolver\DataProvider\Page as CorePage;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Widget\Model\Template\FilterEmulate;
use ScandiPWA\CmsGraphQl\Api\Data\PageInterface;

class Page extends CorePage
{
    /**
     * @param PageRepositoryInterface $pageRepository
     * @param FilterEmulate $widgetFilter
     * @param GetPageByIdentifierInterface $getPageByIdentifier
     */
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository,
        private readonly FilterEmulate $widgetFilter,
        private readonly GetPageByIdentifierInterface $getPageByIdentifier
    ) {
        parent::__construct(
            $pageRepository,
            $widgetFilter,
            $getPageByIdentifier
        );
    }

    /**
     * get page data by page ID.
     * @param int $pageId
     * @return array
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getDataByPageId(int $pageId): array
    {
        $page = $this->pageRepository->getById($pageId);

        return $this->convertPageData($page);
    }

    /**
     * get page data by page identifier.
     * @param string $pageIdentifier
     * @param int $storeId
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDataByPageIdentifier(string $pageIdentifier, int $storeId): array
    {
        $page = $this->getPageByIdentifier->execute($pageIdentifier, $storeId);

        return $this->convertPageData($page);
    }

    /**
     * convert page data
     * @param OriginalPageInterface $page
     * @return array
     * @throws NoSuchEntityException
     */
    public function convertPageData(OriginalPageInterface $page)
    {
        if (false === $page->isActive()) {
            throw new NoSuchEntityException();
        }

        $renderedContent = $this->widgetFilter->filter($page->getContent());

        // page_width is a column this module adds, so the CMS page interface declares no getter for it
        $pageWidth = $page instanceof DataObject
            ? (string)$page->getData(PageInterface::PAGE_WIDTH)
            : '';

        return [
            PageInterface::URL_KEY => $page->getIdentifier(),
            OriginalPageInterface::TITLE => $page->getTitle(),
            OriginalPageInterface::CONTENT => $renderedContent,
            OriginalPageInterface::CONTENT_HEADING => $page->getContentHeading(),
            OriginalPageInterface::PAGE_LAYOUT => $page->getPageLayout(),
            PageInterface::PAGE_WIDTH => $pageWidth ?: 'default',
            OriginalPageInterface::META_TITLE => $page->getMetaTitle(),
            OriginalPageInterface::META_DESCRIPTION => $page->getMetaDescription(),
            OriginalPageInterface::META_KEYWORDS => $page->getMetaKeywords(),
            OriginalPageInterface::PAGE_ID => $page->getId(),
            OriginalPageInterface::IDENTIFIER => $page->getIdentifier(),
        ];
    }
}
