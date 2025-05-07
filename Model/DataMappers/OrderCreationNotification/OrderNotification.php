<?php

namespace JanisCommerce\MagentoConnector\Model\DataMappers\OrderCreationNotification;


use Janis\JanisConnector\Helper\Data;
use Janis\JanisConnector\Model\DataMappers\AbstractAttributeMapper;

class OrderNotification extends AbstractAttributeMapper
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * OrderNotification constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * Body payload builder to sent notification to janis of new order created
     *
     * @param boolean $jsonEncoded
     * @return array|false|string
     */
    public function builtPayload($jsonEncoded = false)
    {
        $payload = [];

        $payload = $this->addToPayload('accountName', $this->helper->getJanisAccountName(), $payload);
        $payload = $this->addToPayload('orderId', $this->obj->getIncrementId(), $payload);
        $payload = $this->addToPayload('externalRef', $this->obj->getId(), $payload);
        $payload = $this->addToPayload('status', $this->obj->getStatus(), $payload);
        $payload = $this->addToPayload('statusCode', $this->obj->getState(), $payload);

        if ($jsonEncoded)
        {
            return json_encode($payload);
        }

        return $payload;
    }
}
