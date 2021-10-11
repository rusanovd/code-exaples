<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Data;

class SmartBoxBillPaidShort
{
    private string $id;
    private float $sum;
    private string $date;

    public function __construct(string $id, float $sum, string $date)
    {
        $this->id = $id;
        $this->sum = $sum;
        $this->date = $date;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSum(): float
    {
        return $this->sum;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public static function createFromArray(array $data): self
    {
        return new self(
            $data['id'] ?? '',
            $data['sum'] ?? 0.00,
            $data['date'] ?? '',
        );
    }
}
