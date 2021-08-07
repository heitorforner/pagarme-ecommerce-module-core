<?php

namespace Pagarme\Core\Kernel\ValueObjects\Configuration;

use Pagarme\Core\Kernel\Abstractions\AbstractValueObject;

class MarketplaceConfig extends AbstractValueObject
{
    const MARKETPLACE_SELLERS = 'marketplace_sellers';
    const MARKETPLACE = 'marketplace';
    const SELLERS = 'sellers';

    /** @var bool */
    private $enabled;

    /** @var string */
    private $responsibilityForProcessingFees;

    /** @var string */
    private $responsibilityForChargebacks;

    /** @var string */
    private $responsibilityForReceivingSplitRemainder;

    /** @var string */
    private $responsibilityForReceivingExtrasAndDiscounts;

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     * @return MarketplaceConfig
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function responsibilityForProcessingFees()
    {
        return $this->responsibilityForProcessingFees;
    }

    /**
     * @param $responsibilityForProcessingFees
     * @return MarketplaceConfig
     */
    public function setResponsibilityForProcessingFees(
        $responsibilityForProcessingFees
    ) {
        $this->responsibilityForProcessingFees = $responsibilityForProcessingFees;
        return $this;
    }

    /**
     * @return string
     */
    public function responsibilityForChargebacks()
    {
        return $this->responsibilityForChargebacks;
    }

    /**
     * @param $responsibilityForChargebacks
     * @return MarketplaceConfig
     */
    public function setResponsibilityForChargebacks(
        $responsibilityForChargebacks
    ) {
        $this->responsibilityForChargebacks = $responsibilityForChargebacks;
        return $this;
    }

    /**
     * @param $option
     * @return bool
     */
    public function getSplitMainOptionConfig($option)
    {
        $responsible = $this->$option();

        if ($responsible == self::MARKETPLACE_SELLERS
            || $responsible == self::MARKETPLACE
        ) {
            return true;
        }

        return false;
    }

    /**
     * @param $option
     * @return bool
     */
    public function getSplitSecondaryOptionConfig($option)
    {
        $responsible = $this->$option();

        if ($responsible == self::MARKETPLACE_SELLERS
            || $responsible == self::SELLERS
        ) {
            return true;
        }

        return false;
    }

    /**
     * To check the structural equality of value objects,
     * this method should be implemented in this class children.
     *
     * @param  $object
     * @return bool
     */
    protected function isEqual($object)
    {
        return
            $this->enabled === $object->isEnabled() &&
            $this->responsibilityForProcessingFees ===
            $object->responsibilityForProcessingFees() &&
            $this->responsibilityForChargebacks ===
            $object->responsibilityForChargebacks();
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return [
            "enabled" => $this->enabled,
            "responsibilityForProcessingFees" => $this->responsibilityForProcessingFees,
            "responsibilityForChargebacks" => $this->responsibilityForChargebacks
        ];
    }
}
