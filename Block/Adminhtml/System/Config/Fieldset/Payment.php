<?php
/*
 * @author Wouter Samaey <wouter.samaey@storefront.agency>
 * @license MIT
 */

declare(strict_types=1);

namespace Storefront\BTCPay\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Payment extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Config\Model\Config
     */
    protected $_backendConfig;

    /**
     * @var SecureHtmlRenderer
     */
    private $secureRenderer;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Config\Model\Config $backendConfig
     * @param array $data
     * @param SecureHtmlRenderer|null $secureRenderer
     */
    public function __construct(
        \Magento\Backend\Block\Context      $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js   $jsHelper,
        \Magento\Config\Model\Config        $backendConfig,
        array                               $data = [],
        ?SecureHtmlRenderer                 $secureRenderer = null
    )
    {
        $this->_backendConfig = $backendConfig;
        $secureRenderer = $secureRenderer ?? ObjectManager::getInstance()->get(SecureHtmlRenderer::class);
        parent::__construct($context, $authSession, $jsHelper, $data, $secureRenderer);
        $this->secureRenderer = $secureRenderer;
    }

    /**
     * Add custom css class
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        $enabledString = $this->_isPaymentEnabled() ? ' enabled' : '';
        return parent::_getFrontendClass($element) . ' with-button' . $enabledString;
    }

    /**
     * Check whether current payment method is enabled
     */
    protected function _isPaymentEnabled(): bool
    {
        $isPaymentEnabled = (bool)(string)$this->_backendConfig->getConfigDataValue(\Storefront\BTCPay\Helper\Data::CONFIG_ROOT . 'active');
        return $isPaymentEnabled;
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html = '<div class="config-heading" >';
        $enabled = $this->_isPaymentEnabled();

        $disabledAttributeString = $enabled ? '' : ' disabled="disabled"';
        $disabledClassString = $enabled ? '' : ' disabled';
        $htmlId = $element->getHtmlId();


        $html .= '<div class="button-container">';
        $html .= '<a class="link-more" href="https://btcpayserver.org" target="_blank">' . __('Learn More') . '</a>';
        $html .='<button type="button"' .
            $disabledAttributeString .
            ' class="button action-configure' .
            $disabledClassString .
            '" id="' . $htmlId . '-head" >' .
            '<span class="state-closed">' . __(
                'Configure'
            ) . '</span><span class="state-opened">' . __(
                'Close'
            ) . '</span></button>';

        $html .= /* @noEscape */
            $this->secureRenderer->renderEventListenerAsTag(
                'onclick',
                "btcpayToggleSolution.call(this, '" . $htmlId . "', '" . $this->getUrl('adminhtml/*/state') .
                "');event.preventDefault();",
                'button#' . $htmlId . '-head'
            );

        $html .= '</div>';
        $html .= '<div class="heading"><strong>' .  __('Accept Bitcoin payments with 0% fees & no third-parties') . '</strong>';
        $html .= '<span class="heading-intro">' .
            __('BTCPay Server is a self-hosted, open-source cryptocurrency payment processor.') .
            '<br/>' . __('It\'s secure, private, censorship-resistant and free.') . '</span>';
        $html .= '</div></div>';

        return $html;
    }

    /**
     * Return header comment part of html for payment solution
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return false
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCollapseState($element)
    {
        return false;
    }

    /**
     * Return extra Js.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getExtraJs($element)
    {
        $script = "require(['jquery', 'prototype'], function(jQuery){
            window.btcpayToggleSolution = function (id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                if ($(this).hasClassName(\"open\")) {
                    \$$(\".with-button button.button\").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName(\"open\")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }
        });";

        return $this->_jsHelper->getScript($script);
    }
}
