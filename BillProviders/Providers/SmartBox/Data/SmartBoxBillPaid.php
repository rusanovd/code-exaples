<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Data;

class SmartBoxBillPaid
{
    private string $billGuid;
    private string $providerGuid;
    private string $status;
    private float $price;
    private string $billPaidDate;
    /**
     * @var SmartBoxBillPaidShort[]
     */
    private array $billsPaid;

    public function __construct(
        string $billGuid,
        string $providerGuid,
        string $status,
        float $price,
        string $billPaidDate,
        array $billsPaid
    ) {
        $this->billGuid = $billGuid;
        $this->providerGuid = $providerGuid;
        $this->status = $status;
        $this->price = $price;
        $this->billPaidDate = $billPaidDate;
        $this->billsPaid = $billsPaid;
    }

    public function getBillGuid(): string
    {
        return $this->billGuid;
    }

    public function getProviderGuid(): string
    {
        return $this->providerGuid;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getBillPaidDate(): string
    {
        return $this->billPaidDate;
    }

    /**
     * @return SmartBoxBillPaidShort[]
     */
    public function getBillsPaid(): array
    {
        return $this->billsPaid;
    }
}
