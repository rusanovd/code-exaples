<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Data;

use CContractBillRequest;
use Exception;
use More\SalonTariff\Service\LicenseService;

class SmartBoxBillFactory
{
    private LicenseService $licenseService;
    private SmartBoxBillDtoFactory $smartBoxBillDtoFactory;

    public function __construct(
        LicenseService $licenseService,
        SmartBoxBillDtoFactory $smartBoxBillDtoFactory
    ) {
        $this->licenseService = $licenseService;
        $this->smartBoxBillDtoFactory = $smartBoxBillDtoFactory;
    }

    /**
     * @param CContractBillRequest $request
     * @param string $billInvoiceGuid
     * @return array
     * @throws Exception
     */
    public function createBillSmartBox(CContractBillRequest $request, string $billInvoiceGuid): array
    {
        $license = $this->licenseService->findById($request->getSalonTariffLinkId());

        if ($license === null) {
            return [];
        }

        $billDto = $this->smartBoxBillDtoFactory->createBillSmartBoxForLicense($request, $license, $billInvoiceGuid);

        return $billDto->toArray();
    }
}
