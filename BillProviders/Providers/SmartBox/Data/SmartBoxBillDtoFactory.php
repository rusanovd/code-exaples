<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Data;

use CContractBillRequest;
use DateTime;
use Exception;
use More\SalonTariff\Data\SalonTariffLink;
use More\SalonTariff\Service\LicenseService;

class SmartBoxBillDtoFactory
{
    private const VALUE_DEFAULT_DISCOUNT = 0;
    private const VALUE_DEFAULT_DISCOUNT_SUM = 0;
    private const VALUE_DEFAULT_AMOUNT = 1;
    private const VALUE_DEFAULT_VAT_SUM = 0;
    private const VALUE_DEFAULT_VAT = 'БезНДС';

    private LicenseService $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    /**
     * @param CContractBillRequest $billRequest
     * @param SalonTariffLink $license
     * @param string $billInvoiceGuid
     * @return SmartBoxBillDto
     * @throws Exception
     */
    public function createBillSmartBoxForLicense(
        CContractBillRequest $billRequest,
        SalonTariffLink $license,
        string $billInvoiceGuid
    ): SmartBoxBillDto {
        return new SmartBoxBillDto(
            $billInvoiceGuid,
            $billRequest->getBillNumber(),
            $billRequest->getName(),
            $billRequest->getInn(),
            $billRequest->getKpp(),
            $billRequest->getAddr(),
            $this->licenseService->getLicenseCodeForDocument($license),
            $this->licenseService->getLicenseTitle($license, true, new DateTime($billRequest->getSaveDate())),
            self::VALUE_DEFAULT_AMOUNT,
            round($license->getPayedCost(), 2),
            round($license->getPayedCost(), 2),
            self::VALUE_DEFAULT_DISCOUNT,
            self::VALUE_DEFAULT_DISCOUNT_SUM,
            self::VALUE_DEFAULT_VAT,
            self::VALUE_DEFAULT_VAT_SUM,
        );
    }
}
