<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright © Scandiweb, Inc. All rights reserved.
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\CmsGraphQl\Api;

interface AttributeHandlerInterface
{
    /**
     * @param string $value
     * @return string
     */
    public function resolve(string $value): string;
}
