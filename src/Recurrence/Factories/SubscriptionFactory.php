<?php

namespace Mundipagg\Core\Recurrence\Factories;

use Mundipagg\Core\Kernel\Abstractions\AbstractEntity;
use Mundipagg\Core\Kernel\Abstractions\AbstractModuleCoreSetup as MPSetup;
use Mundipagg\Core\Kernel\Interfaces\FactoryInterface;
use Mundipagg\Core\Kernel\Interfaces\PlatformOrderInterface;
use Mundipagg\Core\Kernel\ValueObjects\Id\ChargeId;
use Mundipagg\Core\Kernel\ValueObjects\Id\SubscriptionId;
use Mundipagg\Core\Kernel\ValueObjects\PaymentMethod;
use Mundipagg\Core\Recurrence\Aggregates\Charge;
use Mundipagg\Core\Recurrence\Aggregates\Subscription;
use Mundipagg\Core\Recurrence\ValueObjects\Id\PlanId;
use Mundipagg\Core\Recurrence\ValueObjects\SubscriptionStatus;
use Mundipagg\Core\Recurrence\ValueObjects\IntervalValueObject;

class SubscriptionFactory implements FactoryInterface
{
    /**
     * @param array $postData
     * @return AbstractEntity|Subscription
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromPostData($postData)
    {
        $subscription = new Subscription();

        $subscription->setSubscriptionId(new SubscriptionId($postData['id']));
        $subscription->setCode($postData['code']);
        $subscription->setStatus(SubscriptionStatus::{$postData['status']}());
        $subscription->setInstallments($postData['installments']);
        $subscription->setPaymentMethod(PaymentMethod::{$postData['payment_method']}());
        $subscription->setIntervalType(IntervalValueObject::{$postData['interval']}($postData['interval_count']));

        $subscription->setMundipaggId(new SubscriptionId($postData['id']));

        $subscription->setPlatformOrder($this->getPlatformOrder($postData['code']));

        if(isset($postData['current_cycle'])) {
            $cycleFactory = new CycleFactory();
            $cycle = $cycleFactory->createFromPostData($postData['current_cycle']);
            $subscription->setCycle($cycle);
        }

        if (isset($postData['plan_id'])) {
            $subscription->setPlanId(new PlanId($postData['plan_id']));
        }

        return $subscription;
    }

    private function getPlatformOrder($code)
    {
        $orderDecoratorClass =
            MPSetup::get(MPSetup::CONCRETE_PLATFORM_ORDER_DECORATOR_CLASS);

        /**
         * @var PlatformOrderInterface $order
         */
        $order = new $orderDecoratorClass();
        $order->loadByIncrementId($code);

        return $order;
    }

    /**
     * @param array $dbData
     * @return AbstractEntity|Subscription
     * @throws \Mundipagg\Core\Kernel\Exceptions\InvalidParamException
     */
    public function createFromDbData($dbData)
    {
        $subscription = new Subscription();

        $subscription->setId($dbData['id']);
        $subscription->setSubscriptionId(new SubscriptionId($dbData['mundipagg_id']));
        $subscription->setCode($dbData['code']);
        $subscription->setStatus(SubscriptionStatus::{$dbData['status']}());
        $subscription->setInstallments($dbData['installments']);
        $subscription->setPaymentMethod(PaymentMethod::{$dbData['payment_method']}());
        $subscription->setIntervalType(IntervalValueObject::{$dbData['interval_type']}($dbData['interval_count']));

        $subscription->setPlatformOrder($this->getPlatformOrder($dbData['code']));

        $subscription->setMundipaggId(new SubscriptionId($dbData['mundipagg_id']));

        if (isset($dbData['current_cycle'])) {
            $cycleFactory = new CycleFactory();
            $cycle = $cycleFactory->createFromPostData($dbData['current_cycle']);
            $subscription->setCycle($cycle);
        }

        if (isset($dbData['plan_id'])) {
            $subscription->setPlanId(new PlanId($dbData['plan_id']));
        }

        return $subscription;
    }
}
