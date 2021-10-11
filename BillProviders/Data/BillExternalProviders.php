<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Data;

use CCachableIdObject;
use DateTimeInterface;
use Infrastructure\DateTime\DateTimeFormat;
use More\Utils\DateUtils;

class BillExternalProviders extends CCachableIdObject
{
    public const PROVIDER_ID_SMART_BOX = 1;

    public const STATUS_CREATED = 1; // Создан
    public const STATUS_REGISTRATION_IN_PROCESS = 2; // В процессе регистрации
    public const STATUS_REGISTRATION_COMPLETE = 3; // Зарегистрирован
    public const STATUS_IN_PROCESS = 5; // В процессе
    public const STATUS_COMPLETE = 8; // Завершён

    protected static $table_name = 'bills_external_providers';

    protected static $table_fields = [
        'id'                  => 0,
        'provider_id'         => 0, // Идентификатор партнёра.
        'bill_provider_guid'  => '', // Идентификатор платежа партнёра.
        'bill_request_id'     => 0, // Идентификатор платежного документа yclients.
        'bill_invoice_guid'   => '', // универсальный уникальный идентификатор платежа yclients.
        'status'              => 0, // Идентификатор статуса платежа партнёра.
        'date_create'         => null, // Время создания платежа партнёра.'
        'date_ps_payment'     => null, // Время оплаты платежа партнёром.
        'date_payout'         => null, // Время получения оплаченной услуги партнёра.
    ];

    public function getId(): int
    {
        return (int) $this->get('id');
    }

    public function getStatus(): int
    {
        return (int) $this->get('status');
    }

    public function setStatus(int $statusId): BillExternalProviders
    {
        $this->set('status', $statusId);

        return $this;
    }

    public function getProviderId(): int
    {
        return (int) $this->get('provider_id');
    }

    public function setProviderId(int $providerId): BillExternalProviders
    {
        $this->set('provider_id', $providerId);

        return $this;
    }

    public function getBillRequestId(): int
    {
        return (int) $this->get('bill_request_id');
    }

    public function setBillRequestId(int $requestId): BillExternalProviders
    {
        $this->set('bill_request_id', $requestId);

        return $this;
    }

    public function getBillProviderGuid(): string
    {
        return (string) $this->get('bill_provider_guid');
    }

    public function setBillProviderGuid(string $billProviderGuid): BillExternalProviders
    {
        $this->set('bill_provider_guid', $billProviderGuid);

        return $this;
    }

    public function getBillInvoiceGuid(): string
    {
        return (string) $this->get('bill_invoice_guid');
    }

    public function setBillInvoiceGuid(string $billInvoiceGuid): BillExternalProviders
    {
        $this->set('bill_invoice_guid', $billInvoiceGuid);

        return $this;
    }

    public function getDateCreateString(): string
    {
        return (string) $this->get('date_create');
    }

    public function getDateCreate(): ?DateTimeInterface
    {
        if (! DateUtils::isDate($this->getDateCreateString(), DateTimeFormat::DATE_TIME_BD)) {
            return null;
        }

        return DateUtils::createFromBdFormat($this->getDateCreateString());
    }

    public function setDateCreate(?DateTimeInterface $dateCreate = null): BillExternalProviders
    {
        $this->set('date_create', $dateCreate ? $dateCreate->format(DateTimeFormat::DATE_TIME_BD) : null);

        return $this;
    }

    public function getDatePsPaymentString(): string
    {
        return (string) $this->get('date_ps_payment');
    }

    public function getDatePsPayment(): ?DateTimeInterface
    {
        if (! DateUtils::isDate($this->getDatePsPaymentString(), DateTimeFormat::DATE_TIME_BD)) {
            return null;
        }

        return DateUtils::createFromBdFormat($this->getDatePsPaymentString());
    }

    public function setDatePsPayment(?DateTimeInterface $datePsPayment = null): BillExternalProviders
    {
        $this->set('date_ps_payment', $datePsPayment ? $datePsPayment->format(DateTimeFormat::DATE_TIME_BD) : null);

        return $this;
    }

    public function getDatePayOutString(): string
    {
        return (string) $this->get('date_payout');
    }

    public function getDatePayOut(): ?DateTimeInterface
    {
        if (! DateUtils::isDate($this->getDatePayOutString(), DateTimeFormat::DATE_TIME_BD)) {
            return null;
        }

        return DateUtils::createFromBdFormat($this->getDatePayOutString());
    }

    public function setDatePayOut(?DateTimeInterface $datePayOut = null): BillExternalProviders
    {
        $this->set('date_payout', $datePayOut ? $datePayOut->format(DateTimeFormat::DATE_TIME_BD) : null);

        return $this;
    }
}
