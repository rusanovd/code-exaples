<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Data;

class SmartBoxBillDto
{
    private string $billInvoiceGuid;
    private string $billInvoiceNum;
    private string $partnerName;
    private string $partnerInn;
    private string $partnerKpp;
    private string $partnerAddress;
    private string $productId;
    private string $productTitle;
    private int $productAmount;
    private float $productPrice;
    private float $productSum;
    private float $productDiscount;
    private float $productDiscountSum;
    private string $productVat;
    private float $productVatSum;

    public function __construct(
        string $billInvoiceGuid,
        string $billInvoiceNum,
        string $partnerName,
        string $partnerInn,
        string $partnerKpp,
        string $partnerAddress,
        string $productId,
        string $productTitle,
        int $productAmount,
        float $productPrice,
        float $productSum,
        float $productDiscount,
        float $productDiscountSum,
        string $productVat,
        float $productVatSum
    ) {
        $this->billInvoiceGuid = $billInvoiceGuid;
        $this->billInvoiceNum = $billInvoiceNum;
        $this->partnerName = $partnerName;
        $this->partnerInn = $partnerInn;
        $this->partnerKpp = $partnerKpp;
        $this->partnerAddress = $partnerAddress;
        $this->productId = $productId;
        $this->productTitle = $productTitle;
        $this->productAmount = $productAmount;
        $this->productPrice = $productPrice;
        $this->productSum = $productSum;
        $this->productDiscount = $productDiscount;
        $this->productDiscountSum = $productDiscountSum;
        $this->productVat = $productVat;
        $this->productVatSum = $productVatSum;
    }

    public function toArray(): array
    {
        return [
            'leadId'  => $this->billInvoiceGuid,
            'num'     => addslashes($this->billInvoiceNum),
            'partner' => [
                'name'    => addslashes($this->partnerName),
                'inn'     => $this->partnerInn,
                'kpp'     => $this->partnerKpp,
                'address' => addslashes($this->partnerAddress),
            ],
            'goods' => [
                [
                    'productId'   => $this->productId,
                    'serviceName' => addslashes($this->productTitle),
                    'amount'      => $this->productAmount,
                    'price'       => round($this->productPrice, 2),
                    'sum'         => round($this->productSum, 2),
                    'discount'    => round($this->productDiscount, 2),
                    'discountSum' => $this->productDiscountSum,
                    'vat'         => $this->productVat,
                    'vatSum'      => $this->productVatSum,
                ],
            ],
            'printFormType' => 'ForPrint',
        ];
    }
}
