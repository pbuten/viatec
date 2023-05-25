<?php
declare(strict_types=1);

namespace Buten\Viatec\Controller\Adminhtml\Inventory;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\HttpInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Buten\Viatec\Cron\UpdateProducts;

/**
 *
 */
class Index implements HttpPostActionInterface
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var Json
     */
    protected Json $serializer;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Http
     */
    protected Http $http;

    /**
     * @var UpdateProducts
     */
    protected UpdateProducts $updateProducts;

    /**
     * Constructor
     *
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param LoggerInterface $logger
     * @param Http $http
     * @param UpdateProducts $updateProducts
     */
    public function __construct(
        PageFactory $resultPageFactory,
        Json $json,
        LoggerInterface $logger,
        Http $http,
        UpdateProducts $updateProducts
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->serializer = $json;
        $this->logger = $logger;
        $this->http = $http;
        $this->updateProducts = $updateProducts;
    }

    /**
     * Execute view action
     */
    public function execute()
    {
        $this->updateProducts->executeManual();
    }

}
