<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\DataProviers;

use More\Integration\BillProviders\Data\BillExternalProviders;
use More\Integration\BillProviders\Storages\BillExternalProvidersStorage;

class BillProvidersDataProvider
{
    private BillExternalProvidersStorage $billExternalProvidersStorage;

    public function __construct(BillExternalProvidersStorage $billExternalProvidersStorage)
    {
        $this->billExternalProvidersStorage = $billExternalProvidersStorage;
    }

    public function findByRequestId(int $providerId, int $requestId): ?BillExternalProviders
    {
        return $this->billExternalProvidersStorage->findByRequestId($providerId, $requestId);
    }

    public function findByInvoiceGuid(int $providerId, string $invoiceGuid): ?BillExternalProviders
    {
        return $this->billExternalProvidersStorage->findByInvoiceGuid($providerId, $invoiceGuid);
    }
}
