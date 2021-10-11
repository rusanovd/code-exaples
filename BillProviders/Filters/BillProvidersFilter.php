<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Filters;

use CBindedObjectFilter;
use CIntParam;
use CStringParam;
use More\Integration\BillProviders\Data\BillExternalProviders;

class BillProvidersFilter extends CBindedObjectFilter
{
    public const FIELD_ID = 'id';
    public const FIELD_PROVIDER_ID = 'provider_id';
    public const FIELD_BILL_PROVIDER_GUID = 'bill_provider_guid';
    public const FIELD_BILL_REQUEST_ID = 'bill_request_id';
    public const FIELD_BILL_INVOICE_GUID = 'bill_invoice_guid';

    public function __construct($data = null, $prefix = '', $parent_filters = [])
    {
        parent::__construct(
            [
                self::FIELD_ID                 => new CIntParam(0, ['min' => 1], true, true),
                self::FIELD_PROVIDER_ID        => new CIntParam(0, ['min' => 1], true, true),
                self::FIELD_BILL_REQUEST_ID    => new CIntParam(0, ['min' => 1], true, true),
                self::FIELD_BILL_PROVIDER_GUID => new CStringParam('', ['min_length' => 1], true),
                self::FIELD_BILL_INVOICE_GUID  => new CStringParam('', ['min_length' => 1], true),
            ],
            $data,
            $prefix,
            $parent_filters
        );
    }

    public function getMyWhereArr()
    {
        $whereArr = [];

        if (! empty($this->F[self::FIELD_ID])) {
            $whereArr[] = $this->getTableShort() . '.id IN (' . implode(', ', $this->F[self::FIELD_ID]) . ')';
        }

        if (! empty($this->F[self::FIELD_PROVIDER_ID])) {
            $whereArr[] = $this->getTableShort() . '.provider_id IN (' . implode(', ', $this->F[self::FIELD_PROVIDER_ID]) . ')';
        }

        if (! empty($this->F[self::FIELD_BILL_REQUEST_ID])) {
            $whereArr[] = $this->getTableShort() . '.bill_request_id IN (' . implode(', ', $this->F[self::FIELD_BILL_REQUEST_ID]) . ')';
        }

        if (! empty($this->F[self::FIELD_BILL_PROVIDER_GUID])) {
            $whereArr[] = $this->getTableShort() . '.bill_provider_guid  IN (' . implode(', ', array_map('escapeStr', $this->F[self::FIELD_BILL_PROVIDER_GUID])) . ')';
        }

        if (! empty($this->F[self::FIELD_BILL_INVOICE_GUID])) {
            $whereArr[] = $this->getTableShort() . '.bill_invoice_guid  IN (' . implode(', ', array_map('escapeStr', $this->F[self::FIELD_BILL_INVOICE_GUID])) . ')';
        }

        return $whereArr;
    }

    public static function getModelClass(): string
    {
        return BillExternalProviders::class;
    }
}
