<?php

namespace ClassyLlama\AvaTax\Controller\Adminhtml\Address;

use ClassyLlama\AvaTax\Framework\Interaction\Address\Validation as ValidationInteraction;
use ClassyLlama\AvaTax\Model\ValidAddressManagement;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\JsonFactory;

class Validation extends Action
{
    /**
     * @var ValidationInteraction
     */
    protected $validationInteraction = null;

    /**
     * @var ValidAddressManagement
     */
    protected $validAddressManagement = null;

    /**
     * @var CustomerAddressInterfaceFactory
     */
    protected $customerAddressFactory = null;

    /**
     * @var null|DataObjectHelper
     */
    protected $dataObjectHelper = null;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory = null;

    /**
     * Validation constructor
     *
     * @param ValidationInteraction $validationInteraction
     * @param ValidAddressManagement $validAddressManagement
     * @param CustomerAddressInterfaceFactory $customerAddressFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param JsonFactory $resultJsonFactory
     * @param Context $context
     */
    public function __construct(
        ValidationInteraction $validationInteraction,
        ValidAddressManagement $validAddressManagement,
        CustomerAddressInterfaceFactory $customerAddressFactory,
        DataObjectHelper $dataObjectHelper,
        JsonFactory $resultJsonFactory,
        Context $context
    ) {
        $this->validationInteraction = $validationInteraction;
        $this->validAddressManagement = $validAddressManagement;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface|string
     */
    public function execute()
    {
        $customerAddressData = $this->getRequest()->getParam('address');

        $customerAddressDataWithRegion = [];
        $customerAddressDataWithRegion['region']['region'] = $customerAddressData['region'];
        if (isset($customerAddressData['region_code'])) {
            $customerAddressDataWithRegion['region']['region_code'] = $customerAddressData['region_code'];
        }
        if ($customerAddressData['region_id']) {
            $customerAddressDataWithRegion['region']['region_id'] = $customerAddressData['region_id'];
        }
        $customerAddressData = array_merge($customerAddressData, $customerAddressDataWithRegion);

        /**
         * @var \Magento\Customer\Api\Data\AddressInterface $addressDataObject
         */
        $addressDataObject = $this->customerAddressFactory->create();

        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $customerAddressData,
            '\Magento\Customer\Api\Data\AddressInterface'
        );

        $addressValidationResponse = $this->validAddressManagement->saveValidAddress($addressDataObject);
        $resultJson = $this->resultJsonFactory->create();
        if (!is_string($addressValidationResponse)) {
            $resultJson->setData([
                AddressInterface::FIRSTNAME => $addressValidationResponse->getFirstname(),
                AddressInterface::LASTNAME => $addressValidationResponse->getLastname(),
                AddressInterface::STREET => $addressValidationResponse->getStreet(),
                AddressInterface::COUNTRY_ID => $addressValidationResponse->getCountryId(),
                AddressInterface::CITY => $addressValidationResponse->getCity(),
                AddressInterface::REGION_ID => $addressValidationResponse->getRegionId(),
                AddressInterface::REGION => $addressValidationResponse->getRegion(),
                AddressInterface::POSTCODE => $addressValidationResponse->getPostcode()
            ]);
        } else {
            $resultJson->setData($addressValidationResponse);
        }

        return $resultJson;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Customer::manage');
    }
}
