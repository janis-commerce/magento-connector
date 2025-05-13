<?php

namespace JanisCommerce\MagentoConnector\Model;

use JanisCommerce\MagentoConnector\Logger\MagentoConnectorLogger;
use JanisCommerce\MagentoConnector\Helper\Data;
use JanisCommerce\MagentoConnector\Model\DataMappers\OrderCreationNotification\OrderNotification;
use JanisCommerce\MagentoConnector\Util\Rest;

class JanisOrderService extends MagentoConnector
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var MagentoConnectorLogger
     */
    private $MagentoConnectorLogger;
    /**
     * @var OrderNotification
     */
    private $orderNotification;
    /**
     * @var OrderCommentManager
     */
    private $orderCommentManager;

    /**
     * JanisOrderService constructor.
     * @param Rest $rest
     * @param Data $helper
     * @param OrderNotification $orderNotification
     * @param OrderCommentManager $orderCommentManager
     * @param MagentoConnectorLogger $MagentoConnectorLogger
     */
    public function __construct(
        Rest $rest,
        Data $helper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        OrderNotification $orderNotification,
        OrderCommentManager $orderCommentManager,
        MagentoConnectorLogger $MagentoConnectorLogger
    )
    {
        $this->helper = $helper;
        $this->MagentoConnectorLogger = $MagentoConnectorLogger;
        $this->orderNotification = $orderNotification;
        $this->orderCommentManager = $orderCommentManager;
        parent::__construct($rest, $helper, $url, $responseFactory, $MagentoConnectorLogger);
    }

    /**
     * @param $saleOrder
     * @return array|bool|float|int|mixed|string|null
     */
    public function sendOrderCreationNotification($saleOrder)
    {
        $this->orderNotification->setObj($saleOrder);

        // Getting create order payload
        $payload = $this->orderNotification->builtPayload(true);

        $this->MagentoConnectorLogger->info('*************** Order Notification ***************');
        $this->MagentoConnectorLogger->info('Order id: ' . $saleOrder->getId() .' sended.');

        $response = $this->post(
            $this->helper->getJanisEndpointToNotifyNewOrder(),
            $payload
        );

        // Saving order comment
        if ( isset($response['SendMessageResponse']['ResponseMetadata']['RequestId']) )
        {
            $this->orderCommentManager->saveComment($saleOrder, print_r($response['SendMessageResponse']['ResponseMetadata']['RequestId'], true));
        } else {
            $this->orderCommentManager->saveComment($saleOrder, print_r($response, true));
        }

        $saleOrder->setIsSyncWithJanis(1);
        $saleOrder->save();

        return $response;
    }
}
