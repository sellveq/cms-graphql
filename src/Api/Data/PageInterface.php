<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright © 2018 Scandiweb, Ltd (https://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\CmsGraphQl\Api\Data;

use Magento\Cms\Api\Data\PageInterface as CorePageInterface;

interface PageInterface extends CorePageInterface
{
    public const string URL_KEY = 'url_key';
    public const string PAGE_WIDTH = 'page_width';
}
