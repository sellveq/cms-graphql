<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright © 2022 Scandiweb, Ltd (https://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace ScandiPWA\CmsGraphQl\Plugin;

use Magento\Framework\App\AreaInterface;
use Magento\Framework\App\AreaList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use ScandiPWA\PersistedQuery\Plugin\PersistedQuery;

class InitGraphQlTranslations
{
    /**
     * @param AreaList $areaList
     * @param State $appState
     */
    public function __construct(
        private readonly AreaList $areaList,
        private readonly State $appState
    ) {}

    /**
     * initialize GraphQL translations before persisted query request processing.
     * @param PersistedQuery $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws LocalizedException
     */
    public function beforeProcessRequest(
        PersistedQuery $subject,
        RequestInterface $request
    ) {
        $this->initTranslations();
    }

    /**
     * initialize translations for the current area.
     * @return void
     * @throws LocalizedException
     */
    protected function initTranslations()
    {
        $area = $this->areaList->getArea($this->appState->getAreaCode());

        $area?->load(AreaInterface::PART_TRANSLATE);
    }
}
