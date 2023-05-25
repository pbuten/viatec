<?php
declare(strict_types=1);

namespace Buten\Viatec\Cron;

use Buten\Viatec\Model\Inventory\GetXml;
use Buten\Viatec\Model\Inventory\Attributes;
use Psr\Log\LoggerInterface;
use Buten\Viatec\Model\Inventory\Categories;
use Buten\Viatec\Model\Inventory\Products;
use Buten\Viatec\Helper\Data;
use Buten\Viatec\Helper\ViatecConfig;
use Buten\Viatec\Helper\Email;

class UpdateProducts
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var GetXml
     */
    protected GetXml $getXml;
    /**
     * @var Categories
     */
    protected Categories $categories;
    /**
     * @var Products
     */
    protected Products $products;
    /**
     * @var Data
     */
    protected Data $data;
    /**
     * @var Attributes
     */
    protected Attributes $attributes;
    /**
     * @var Email
     */
    protected Email $email;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param GetXml $getXml
     * @param Categories $categories
     * @param Products $products
     * @param Data $data
     * @param Attributes $attributes
     * @param Email $email
     */
    public function __construct(
        LoggerInterface $logger,
        GetXml          $getXml,
        Categories      $categories,
        Products        $products,
        Data            $data,
        Attributes $attributes,
        Email $email
    )
    {
        $this->logger = $logger;
        $this->getXml = $getXml;
        $this->categories = $categories;
        $this->products = $products;
        $this->data = $data;
        $this->attributes = $attributes;
        $this->email = $email;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->Info("Cronjob UpdateProducts is started");
        $fileArray = $this->getXml->getFileArray();
        $dateTime = $fileArray['date'] . ' ' . $fileArray['time'];
        if ($this->data->getConfigValue(ViatecConfig::DATE) != $dateTime) {
            $this->categories->execute($fileArray['categories']['category']);
            $this->products->saveProducts($fileArray['products']['product']);
            $this->data->setConfigValue(ViatecConfig::DATE, $dateTime);
            $this->logger->Info("Cronjob UpdateProducts is executed. Products Updated");
        } else {
            $this->logger->Info("Cronjob UpdateProducts is executed. File wasn't updated on remote");
        }
        $this->email->sendEmail();
        $this->logger->Info("Email sent");
    }

    /**
     * @return void
     */
    public function executeManual()
    {
        $this->logger->Info("Viatec Manual run is started.");
        $fileArray = $this->getXml->getFileArray();
        $dateTime = $fileArray['date'] . ' ' . $fileArray['time'];

        $this->attributes->updateAttributes($fileArray['products']['product']);
        $this->attributes->updateAttributes($fileArray['products']['product']);
        $this->attributes->updateAttributes($fileArray['products']['product']);
        $this->logger->Info("Viatec attributes are done.");
        $this->categories->execute($fileArray['categories']['category']);
        $this->logger->Info("Categories done");
        $this->products->saveProducts($fileArray['products']['product']);
        $this->logger->Info("Products done");
        $this->data->setConfigValue(ViatecConfig::DATE, $dateTime);
        $this->logger->Info("Viatec Manual run is executed. Products Updated");
        $this->email->sendEmail();
        $this->logger->Info("Email sent");
    }
}

