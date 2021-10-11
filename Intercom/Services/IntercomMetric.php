<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use Infrastructure\Metrics\Data\Metric;
use Infrastructure\Metrics\Data\MetricsDict;

class IntercomMetric
{
    // api request
    public const METRIC_REQUEST_CONTACT = 'request_contact';
    public const METRIC_REQUEST_COMPANY = 'request_company';
    public const METRIC_REQUEST_CHAT = 'request_chat';
    // event contact
    public const METRIC_EVENT_CONTACT_SETTINGS_CHANGED = 'contact_settings_changed';
    public const METRIC_EVENT_CONTACT_AMO_CHANGED = 'contact_amo_changed';
    // event company
    public const METRIC_EVENT_COMPANY_INFO_CHANGED = 'company_info_changed';
    public const METRIC_EVENT_COMPANY_MAIN_SETTINGS_CHANGED = 'company_main_settings_changed';
    public const METRIC_EVENT_COMPANY_BUSINESS_CHANGED = 'company_business_changed';
    public const METRIC_EVENT_COMPANY_SETTINGS_CREATED = 'company_settings_created';
    public const METRIC_EVENT_COMPANY_LICENSE_CHANGED = 'company_license_changed';
    public const METRIC_EVENT_COMPANY_LICENSE_ACTIVATED = 'company_license_activated';
    public const METRIC_EVENT_COMPANY_TARIFF_CHANGED = 'company_tariff_changed';
    public const METRIC_EVENT_COMPANY_ACTIVATED = 'company_activated';
    public const METRIC_EVENT_COMPANY_DEACTIVATED = 'company_deactivated';
    public const METRIC_EVENT_COMPANY_MANAGER_CHANGED = 'company_manager_changed';
    public const METRIC_EVENT_COMPANY_CONSULTING_CHANGED = 'company_consulting_changed';
    public const METRIC_EVENT_COMPANY_MASTERS_NUM_CHANGED = 'company_masters_num_changed';

    /**
     * @param string $eventName
     * @return Metric
     */
    public static function createEventMetric(string $eventName): Metric
    {
        return new Metric([MetricsDict::RESOURCE_INTERCOM, MetricsDict::ACTION_EVENT, $eventName]);
    }

    /**
     * @param string $methodName
     * @return Metric
     */
    public static function createRequestSuccessMetric(string $methodName): Metric
    {
        return new Metric([MetricsDict::RESOURCE_INTERCOM, MetricsDict::ACTION_REQUEST, $methodName, MetricsDict::STATUS_SUCCESS]);
    }

    /**
     * @param string $methodName
     * @return Metric
     */
    public static function createRequestErrorMetric(string $methodName): Metric
    {
        return new Metric([MetricsDict::RESOURCE_INTERCOM, MetricsDict::ACTION_REQUEST, $methodName, MetricsDict::STATUS_ERROR]);
    }
}
