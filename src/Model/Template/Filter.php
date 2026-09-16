<?php

/**
 * @category    ScandiPWA
 * @package     ScandiPWA_CmsGraphQl
 * @copyright   Copyright 2013 Adobe. All Rights Reserved.
 * @copyright   Copyright © 2018 Scandiweb, Ltd (https://scandiweb.com)
 * @copyright   Modifications © Selveq. All rights reserved.
 * @license     OSL-3.0 (Open Software License ("OSL") v. 3.0)
 * See LICENSE for license details.
 */

namespace ScandiPWA\CmsGraphQl\Model\Template;

use Magento\Email\Model\Template\Css;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Css\PreProcessor\Adapter\CssInliner;
use Magento\Framework\Escaper;
use Magento\Framework\Filesystem;
use Magento\Framework\Filter\VariableResolverInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Variable\Model\Source\Variables;
use Magento\Variable\Model\VariableFactory;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Model\ResourceModel\Widget as WidgetResource;
use Magento\Widget\Model\Template\FilterEmulate;
use Magento\Widget\Model\Widget;
use Psr\Log\LoggerInterface;
use ScandiPWA\CmsGraphQl\Api\AttributeHandlerInterface;

class Filter extends FilterEmulate
{
    // a name reaches the theme as an attribute name, and the emitted form is name='value', so only a key may sit there
    private const string PARAM_NAME_PATTERN = '/^[A-Za-z0-9_]+$/';

    private const string PARAM_NAME_GUARD = 'widget_param_name';

    private const string EXEMPT_VALUE_GUARD = 'widget_exempt_value';

    /**
     * keys that will not be escaped in custom widget html output
     * @var string[]
     */
    protected $widgetParamsWhitelist;

    /**
     * @param StringUtils $string
     * @param LoggerInterface $logger
     * @param Escaper $escaper
     * @param Repository $assetRepo
     * @param ScopeConfigInterface $scopeConfig
     * @param VariableFactory $coreVariableFactory
     * @param StoreManagerInterface $storeManager
     * @param LayoutInterface $layout
     * @param LayoutFactory $layoutFactory
     * @param State $appState
     * @param UrlInterface $urlModel
     * @param Variables $configVariables
     * @param VariableResolverInterface $variableResolver
     * @param Css\Processor $cssProcessor
     * @param Filesystem $pubDirectory
     * @param CssInliner $cssInliner
     * @param WidgetResource $widgetResource
     * @param Widget $widget
     * @param array $variables
     * @param array $directiveProcessors
     * @param string[] $availableFilters
     * @param string[] $widgetUnescapedParams
     * @param AttributeHandlerInterface[] $widgetCustomParamsHandlers
     */
    public function __construct(
        StringUtils $string,
        LoggerInterface $logger,
        Escaper $escaper,
        Repository $assetRepo,
        ScopeConfigInterface $scopeConfig,
        VariableFactory $coreVariableFactory,
        StoreManagerInterface $storeManager,
        LayoutInterface $layout,
        LayoutFactory $layoutFactory,
        State $appState,
        UrlInterface $urlModel,
        Variables $configVariables,
        VariableResolverInterface $variableResolver,
        Css\Processor $cssProcessor,
        Filesystem $pubDirectory,
        CssInliner $cssInliner,
        WidgetResource $widgetResource,
        Widget $widget,
        $variables = [],
        array $directiveProcessors = [],
        private readonly array $availableFilters = [],
        array $widgetUnescapedParams = [],
        private readonly array $widgetCustomParamsHandlers = []
    ) {
        parent::__construct(
            $string,
            $logger,
            $escaper,
            $assetRepo,
            $scopeConfig,
            $coreVariableFactory,
            $storeManager,
            $layout,
            $layoutFactory,
            $appState,
            $urlModel,
            $configVariables,
            $variableResolver,
            $cssProcessor,
            $pubDirectory,
            $cssInliner,
            $widgetResource,
            $widget,
            $variables,
            $directiveProcessors
        );

        $this->widgetParamsWhitelist = $widgetUnescapedParams;
    }

    /**
     * general method for generate widget
     * @param string[] $construction
     * @return string
     */
    public function generateWidget($construction)
    {
        $params = $this->getParameters($construction[2]);

        $name = null;
        if (isset($params['name'])) {
            $name = $params['name'];
        }

        if (isset($this->_storeId) && !isset($params['store_id'])) {
            $params['store_id'] = $this->_storeId;
        }

        if (!empty($params['type'])) {
            $type = $params['type'];
        } elseif (!empty($params['id'])) {
            $preConfigured = $this->_widgetResource->loadPreconfiguredWidget($params['id']);
            $type = $preConfigured['widget_type'];
            $params = $preConfigured['parameters'];
        } else {
            return '';
        }

        // we have no other way to avoid fatal errors for type like 'cms/widget__link', '_cms/widget_link' etc.
        $xml = $this->_widget->getWidgetByClassType($type);
        if ($xml === null) {
            return '';
        }

        // the {{widget id="N"}} branch replaced $params, so the match is on $type and not on $params['type']
        if ($widgetName = array_search($type, $this->availableFilters)) {
            return $this->widgetToHtml($params, $widgetName);
        }

        $widget = $this->_layout->createBlock($type, $name, ['data' => $params]);
        if (!$widget instanceof BlockInterface) {
            return '';
        }

        return $widget->toHtml();
    }

    /**
     * generates widget html-like instructions
     * @param string[] $params
     * @param string $widgetName
     * @return string
     */
    public function widgetToHtml($params, $widgetName)
    {
        unset($params['template']);
        $params['type'] = $widgetName;

        $paramsList = [];
        foreach ($params as $key => $value) {
            if (!preg_match(self::PARAM_NAME_PATTERN, (string)$key)) {
                $this->_logger->warning(sprintf(
                    '%s: %s dropped one parameter, name length %d',
                    self::PARAM_NAME_GUARD,
                    $widgetName,
                    strlen((string)$key)
                ));

                continue;
            }

            if (key_exists($key, $this->widgetCustomParamsHandlers)) {
                $resolved = $this->widgetCustomParamsHandlers[$key]->resolve($value);
                $value = $this->guardExemptValue($widgetName, $key, $resolved);
            } elseif (in_array($key, $this->widgetParamsWhitelist)) {
                $value = $this->guardExemptValue($widgetName, $key, (string)$value);
            } else {
                $value = $this->_escaper->escapeHtmlAttr($value);
            }

            $paramsList[] = "$key='$value'";
        }

        $attributes = implode(' ', $paramsList);

        return "<widget $attributes></widget>";
    }

    /**
     * a whitelisted key and a handler's return skip escapeHtmlAttr(), so the quote that ends the attribute is checked
     * @param string $widgetName
     * @param string $key
     * @param string $value
     * @return string
     */
    private function guardExemptValue(string $widgetName, string $key, string $value): string
    {
        if (!str_contains($value, "'")) {
            return $value;
        }

        $this->_logger->warning(sprintf(
            '%s: %s escaped an exempt value on %s',
            self::EXEMPT_VALUE_GUARD,
            $widgetName,
            $key
        ));

        return $this->_escaper->escapeHtmlAttr($value);
    }
}
