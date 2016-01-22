<?php
/**
 * @category    ClassyLlama
 * @package     AvaTax
 * @copyright   Copyright (c) 2016 Matt Johnson & Classy Llama Studios, LLC
 */

namespace ClassyLlama\AvaTax\Block\Adminhtml\Queue;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class ClearButton
 */
class ClearButton implements ButtonProviderInterface
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     */
    public function __construct(Context $context)
    {
        $this->urlBuilder = $context->getUrlBuilder();
    }

    /**
     * Get button data
     *
     * @return array
     */
    public function getButtonData()
    {
        $message = __(
            'This will clear any completed queued transmissions that have already been sent to AvaTax. ' .
            'This will also clear any failed transmissions that are older then the lifetime set in configuration. ' .
            'Any failed transmissions will need to be manually adjusted and entered into AvaTax directly. ' .
            'Do you want to continue?'
        );
        return [
            'label' => __('Clear Queue Now'),
            'on_click' => "confirmSetLocation('{$message}', '{$this->getButtonUrl()}')"
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    protected function getButtonUrl()
    {
        return $this->urlBuilder->getUrl('*/*/clear');
    }
}
