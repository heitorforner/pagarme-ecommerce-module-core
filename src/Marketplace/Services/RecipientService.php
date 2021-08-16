<?php

namespace Pagarme\Core\Marketplace\Services;

use MundiAPILib\MundiAPIClient;
use Pagarme\Core\Kernel\Abstractions\AbstractModuleCoreSetup;
use Pagarme\Core\Kernel\Services\LogService;
use Pagarme\Core\Marketplace\Aggregates\Recipient;
use Pagarme\Core\Marketplace\Factories\RecipientFactory;
use Pagarme\Core\Marketplace\Repositories\RecipientRepository;

class RecipientService
{
    /** @var LogService  */
    protected $logService;

    protected $config;

    public function __construct()
    {
        AbstractModuleCoreSetup::bootstrap();

        $this->config = AbstractModuleCoreSetup::getModuleConfiguration();

        $secretKey = null;
        if ($this->config->getSecretKey() != null) {
            $secretKey = $this->config->getSecretKey()->getValue();
        }

        $password = '';

        \MundiAPILib\Configuration::$basicAuthPassword = '';

        $this->mundipaggApi = new MundiAPIClient($secretKey, $password);
    }

    public function saveFormRecipient($formData)
    {
        $recipientFactory = $this->getRecipientFactory();
        $recipient = $recipientFactory->createFromPostData($formData);

        $result = $this->createRecipientAtPagarme($recipient);

        return $this->saveRecipient($recipient);
    }

    public function createRecipientAtPagarme(Recipient $recipient)
    {
        $createRecipientRequest = $recipient->convertToSdkRequest();
        $recipientController = $this->mundipaggApi->getRecipients();

        try {
            $logService = $this->getLogService();
            $logService->info(
                'Create recipient request: ' .
                json_encode($createRecipientRequest, JSON_PRETTY_PRINT)
            );

            $result = $recipientController->createRecipient(
                $createRecipientRequest,
                $this->config->getSecretKey()->getValue()
            );

            $logService->info(
                'Create recipient response: ' .
                json_encode($result, JSON_PRETTY_PRINT)
            );

            return $result;
        } catch (\Exception $exception) {
            return $exception->getMessage();
        }
    }

    public function saveRecipient(Recipient $recipient)
    {
        $this->getLogService()->info("Creating new recipient at platform");

        $recipientRepository = $this->getRecipientRepository();

        $recipientRepository->save($recipient);
        $this->getLogService()->info("Recipient created: " . $recipient->getId());

        return $recipient;
    }

    public function getRecipientFactory()
    {
        return new RecipientFactory();
    }

    public function getRecipientRepository()
    {
        return new RecipientRepository();
    }

    public function getLogService()
    {
        return new LogService(
            'ProductSubscriptionService',
            true
        );
    }

}
