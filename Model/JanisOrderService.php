<?php

namespace Janis\JanisConnector\Model;

use Janis\JanisConnector\Logger\JanisConnectorLogger;
use Janis\JanisConnector\Helper\Data;
use Janis\JanisConnector\Model\DataMappers\OrderCreationNotification\OrderNotification;
use Janis\JanisConnector\Util\Rest;

class JanisOrderService extends JanisConnector
{
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var JanisConnectorLogger
     */
    private $janisConnectorLogger;
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
     * @param JanisConnectorLogger $janisConnectorLogger
     */
    public function __construct(
        Rest $rest,
        Data $helper,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        OrderNotification $orderNotification,
        OrderCommentManager $orderCommentManager,
        JanisConnectorLogger $janisConnectorLogger
    )
    {
        $this->helper = $helper;
        $this->janisConnectorLogger = $janisConnectorLogger;
        $this->orderNotification = $orderNotification;
        $this->orderCommentManager = $orderCommentManager;
        parent::__construct($rest, $helper, $url, $responseFactory, $janisConnectorLogger);
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

        $this->janisConnectorLogger->info('*************** Order Notification ***************');
        $this->janisConnectorLogger->info('Order id: ' . $saleOrder->getId() .' sended.');

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
