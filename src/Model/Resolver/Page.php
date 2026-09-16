<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

declare(strict_types=1);

namespace ScandiPWA\CmsGraphQl\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\Page as CorePage;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Page extends CorePage
{
    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (isset($args['url_key']) && !isset($args['id']) && !isset($args['identifier'])) {
            $args['identifier'] = $args['url_key'];
        }

        return parent::resolve($field, $context, $info, $value, $args);
    }
}
