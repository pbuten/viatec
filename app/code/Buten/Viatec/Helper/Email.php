<?php

namespace Buten\Viatec\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Psr\Log\LoggerInterface;

class Email
{
    private TransportBuilder $transportBuilder;
    protected Data $data;
    protected LoggerInterface $logger;

    public function __construct(
        TransportBuilder $transportBuilder,
        Data $data,
        LoggerInterface $logger
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->data = $data;
        $this->logger = $logger;
    }

    public function sendEmail()
    {
        $skus = $this->data->getConfigValue(ViatecConfig::NEW_PRODUCTS_CONFIG);
        if (!$skus) {
            return;
        }
        $fileDate = $this->data->getConfigValue(ViatecConfig::DATE);
        $recepients = explode(',', $this->data->getConfigValue(ViatecConfig::EMAILS_CONFIG));
        if (!$recepients) {
            return;
        }
        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('email_template_viatec') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars([
                    'skus' => $skus,
                    'file_date' => $fileDate
                ])
                ->setFrom('general')
                ->addTo(
                    $recepients[0],
                    'Admin'
                );

            if (count($recepients) > 1) {
                foreach ($recepients as $key => $recepient) {
                    if ($key > 0) {
                        $transport->addCc($recepient);
                    }
                }
            }
            $transport->getTransport()->sendMessage();
            $this->data->setConfigValue(ViatecConfig::NEW_PRODUCTS_CONFIG, '');
        } catch (LocalizedException $exception) {
            $this->logger->error("Couldn't send mail", [
                'exception' => $exception->getMessage()
            ]);
        }
    }
}
