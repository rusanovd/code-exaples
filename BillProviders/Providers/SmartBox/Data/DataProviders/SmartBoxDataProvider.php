<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Data\DataProviders;

use More\Integration\BillProviders\Data\BillExternalProviders;
use More\Integration\BillProviders\DataProviers\BillProvidersDataProvider;

class SmartBoxDataProvider
{
    private BillProvidersDataProvider $billProvidersDataProvider;

    public function __construct(BillProvidersDataProvider $billProvidersDataProvider)
    {
        $this->billProvidersDataProvider = $billProvidersDataProvider;
    }

    public function findBillByRequestId(int $requestId): ?BillExternalProviders
    {
        return $this->billProvidersDataProvider->findByRequestId(BillExternalProviders::PROVIDER_ID_SMART_BOX, $requestId);
    }

    public function findBillByInvoiceGuid(string $invoiceGuid): ?BillExternalProviders
    {
        return $this->billProvidersDataProvider->findByInvoiceGuid(BillExternalProviders::PROVIDER_ID_SMART_BOX, $invoiceGuid);
    }
}
