<?php

declare(strict_types=1);

namespace More\Integration\BillProviders\Providers\SmartBox\Request;

use More\Integration\BillProviders\Providers\SmartBox\Data\SmartBoxBillPaid;
use More\Integration\BillProviders\Providers\SmartBox\Data\SmartBoxBillPaidShort;
use More\Integration\BillProviders\Providers\SmartBox\Exceptions\SmartBoxBadParamsException;

class SmartBoxRequestBillPaid
{
    private SmartBoxBillPaid $smartBoxBillPaid;

    public function __construct(SmartBoxBillPaid $smartBoxBillPaid)
    {
        $this->smartBoxBillPaid = $smartBoxBillPaid;
    }

    public function getSmartBoxBillPaid(): SmartBoxBillPaid
    {
        return $this->smartBoxBillPaid;
    }

    public static function createFromArray(array $data): SmartBoxBillPaid
    {
        if (empty($data['order_id'])) {
            throw new SmartBoxBadParamsException('Empty order id');
        }

        $billsPaid = [];
        if (! empty($data['paid_data'])) {
            foreach ($data['paid_data'] as $paidItem) {
                if (empty($paidItem)) {
                    continue;
                }
                $billsPaid[] = SmartBoxBillPaidShort::createFromArray((array) $paidItem);
            }
        }

        return new SmartBoxBillPaid(
            (string) ($data['order_id'] ?? ''),
            (string) ($data['order_guid'] ?? ''),
            (string) ($data['state'] ?? ''),
            (float) ($data['sum'] ?? 0.00),
            (string) ($data['paid_date'] ?? ''),
            $billsPaid,
        );
    }
}
