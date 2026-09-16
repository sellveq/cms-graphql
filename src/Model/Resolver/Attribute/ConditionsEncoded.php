<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright © 2018 Scandiweb, Ltd (https://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\CmsGraphQl\Model\Resolver\Attribute;

use ScandiPWA\CmsGraphQl\Api\AttributeHandlerInterface;

class ConditionsEncoded implements AttributeHandlerInterface
{
    /**
     * {@inheritdoc}
     */
    public function resolve(string $value): string
    {
        return base64_encode($value);
    }
}
