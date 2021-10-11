<?php

declare(strict_types=1);

namespace More\Amo\Services;

use Infrastructure\Metrics\Data\Metric;
use Infrastructure\Metrics\Data\MetricsDict;

class AmoMetric
{
    // WEBHOOKS
    public const METRIC_WEBHOOK_RESPONSIBLE_MANGER = 'webhook_responsible_manager';
    public const METRIC_WEBHOOK_CONSULTING_STATUS = 'webhook_consulting_status';
    // API
    // contacts
    public const METRIC_REQUEST_CONTACT_CREATE = 'contact_create';
    public const METRIC_REQUEST_CONTACT_CREATE_BY_DTO = 'contact_create_by_dto';
    public const METRIC_REQUEST_CONTACT_UPDATE = 'contact_update';
    public const METRIC_REQUEST_CONTACT_UPDATE_LEADS = 'contact_update_leads';
    public const METRIC_REQUEST_CONTACT_FIND_BY_ID = 'contact_find_by_id';
    public const METRIC_REQUEST_CONTACT_FIND_BY_IDS = 'contact_find_by_ids';
    public const METRIC_REQUEST_CONTACT_FIND_BY_PHONE = 'contact_find_by_phone';
    public const METRIC_REQUEST_CONTACT_GET = 'contact_get';
    // task
    public const METRIC_REQUEST_TASK_CREATE = 'task_create';
    public const METRIC_REQUEST_ACCOUNT_FIND_BY_ID = 'account_find_by_id';
    // leads
    public const METRIC_REQUEST_LEAD_GET = 'lead_get';
    public const METRIC_REQUEST_LEAD_UPDATE = 'lead_update';
    public const METRIC_REQUEST_LEAD_CREATE = 'lead_create';
    public const METRIC_REQUEST_LEAD_FIND_BY_ID = 'lead_find_by_id';
    //links
    public const METRIC_REQUEST_LINK_FIND_BY_LEAD_ID = 'link_find_by_lead_id';

    /**
     * @param string $methodName
     * @return Metric
     */
    public static function createWebhookErrorMetric(string $methodName): Metric
    {
        return new Metric([MetricsDict::RESOURCE_AMO, MetricsDict::ACTION_WEBHOOK, $methodName, MetricsDict::STATUS_ERROR]);
    }

    /**
     * @param string $methodName
     * @return Metric
     */
    public static function createWebhookSuccessMetric(string $methodName): Metric
    {
        return new Metric([MetricsDict::RESOURCE_AMO, MetricsDict::ACTION_WEBHOOK, $methodName, MetricsDict::STATUS_SUCCESS]);
    }

    /**
     * @param string $methodName
     * @return Metric
     */
    public static function createRequestMetric(string $methodName): Metric
    {
        return new Metric([MetricsDict::RESOURCE_AMO, MetricsDict::ACTION_REQUEST, $methodName, MetricsDict::STATUS_SUCCESS]);
    }

    /**
     * @param string $methodName
     * @return Metric
     */
    public static function createQueueLeadMetric(string $methodName): Metric
    {
        return new Metric([MetricsDict::RESOURCE_AMO, MetricsDict::RESOURCE_QUEUE, $methodName, MetricsDict::STATUS_SUCCESS]);
    }
}
