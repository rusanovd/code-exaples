<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

class IntercomFieldsMapper
{
    // inner system fields
    public const FIELD_TYPE = 'type';
    public const FIELD_NEED_LINK_DB = 'is_need_link_db';
    public const FIELD_USER_SALON_ID = 'user_salon_id';

    // outer system fields
    public const FIELD_COMPANIES = 'companies';
    public const FIELD_COMPANY = 'company';
    public const FIELD_CONTACT = 'contact';
    public const FIELD_CUSTOM_ATTRIBUTES = 'custom_attributes';
    public const FIELD_SCRIPT_FLAG = 'script_flag';

    // chat fields
    public const FIELD_APP_ID = 'app_id';
    public const FIELD_APP_LAUNCHER = 'custom_launcher_selector';
    public const FIELD_USER_HASH = 'user_hash';

    // identification fields
    public const FIELD_ID = 'id';
    public const FIELD_NAME = 'name';
    public const FIELD_EMAIL = 'email';
    public const FIELD_PHONE = 'phone';
    public const FIELD_COMPANY_ID = 'company_id';
    public const FIELD_USER_ID = 'user_id';

    // fields
    public const FIELD_DELETED = 'deleted';
    public const FIELD_HAS_ACTIVE_SALON = 'has_active_salon';
    public const FIELD_ACTIVE = 'active';
    public const FIELD_POSITION = 'position';
    public const FIELD_PROMO = 'promo';
    public const FIELD_VIP = 'vip';
    public const FIELD_WEBSITE = 'website';

    // location
    public const FIELD_COUNTRY_ID = 'country_id';
    public const FIELD_COUNTRY_TITLE = 'country_title';
    public const FIELD_COUNTRY_TYPE = 'country_type';
    public const FIELD_COUNTRY_GROUP_ID = 'country_group_id';
    public const FIELD_CITY_ID = 'city_id';
    public const FIELD_CITY_TITLE = 'city_title';

    // business
    public const FIELD_INDUSTRY = 'industry';
    public const FIELD_INDUSTRY_GROUP = 'Company_industry_group';

    // onboarding
    public const FIELD_PLACES_SIZE = 'Initial_number_of_branches';
    public const FIELD_STAFF_COUNT_1 = 'Initial_company_size';
    public const FIELD_PRIMARY_USE_CASE = 'Primary_Use_Case';
    public const FIELD_PROVIDE_SERVICES_BY_MYSELF = 'provide_services_by_myself';
    public const FIELD_REFERRAL_SOURCE = 'referral_source';

    public const FIELD_IS_INDIVIDUAL = 'is_individual';
    public const FIELD_MASTERS = 'masters';
    public const FIELD_FILIALS = 'filials';

    // utm
    public const FIELD_UTM_MEDIUM = 'utm_medium';
    public const FIELD_UTM_SOURCE = 'utm_source';
    public const FIELD_UTM_TERM = 'utm_term';
    public const FIELD_UTM_CONTENT = 'utm_content';
    public const FIELD_UTM_CAMPAIGN = 'utm_campaign';

    public const FIELD_SUBSCRIBED_AT = 'subscribed_at';
    public const FIELD_CREATED_AT = 'created_at';
    public const FIELD_REMOTE_CREATED_AT = 'remote_created_at';
    public const FIELD_UPDATED_AT = 'updated_at';

    public const FIELD_LAST_ACTIVATION_DATE = 'last_activation_date';
    public const FIELD_DEACTIVATION_DATE = 'Deactivation Date';
    public const FIELD_LAST_DEACTIVATION_DATE = 'last_disactivation_date';

    public const FIELD_LAST_SEEN_DATE = 'Company last seen';
    public const FIELD_LAST_REQUEST_DATE = 'last_request_at';
    public const FIELD_LAST_RECORD_DATE = 'last_record_create_date';
    public const FIELD_BRANCH_TIMEZONE = 'Branch Timezone';

    // letters fields
    public const FIELD_NEWS_LETTERS = 'newsletters';
    public const FIELD_MARKETING_LETTERS = 'marketingletters';
    public const FIELD_INFO_LETTERS = 'infoletters';

    // plan fields
    public const FIELD_MONTHLY_SPEND = 'monthly_spend';
    public const FIELD_AVG_SPEND = 'avg_spends';
    public const FIELD_PLAN = 'plan';
    public const FIELD_PLAN_SIZE = 'Plan size';
    public const FIELD_PLAN_ABS = 'plan_abs';
    public const FIELD_PLAN_RUB = 'plan_rub';
    public const FIELD_PLAN_NAME = 'Plan Name';
    public const FIELD_BALANCE_ABS = 'balance_abs';
    public const FIELD_BALANCE_RUB = 'balance_rub';

    // license paid options
    public const FIELD_NOTIFICATIONS_PAID = 'Notifications_paid';
    public const FIELD_KKM_PAID = 'KKM_server_paid';

    public const FIELD_FREE_TRIAL = 'Free_trial';
    public const FIELD_TARIFF_DISCOUNT = 'tariff_discount';
    public const FIELD_LAST_PAID_DATE = 'Last_paid_date';

    // integrations
    public const FIELD_ZENDESK_ID = 'zendesk_id';
    public const FIELD_INTERCOM_ID = 'intercom_id';
    public const FIELD_AMO_LEAD_LINK = 'Amo_lead_link';
    public const FIELD_AMO_CONTACT_LINK = 'Amo_contact_link';

    // consulting
    public const FIELD_CONSULTING_MANAGER_NAME = 'Consulting manager';
    public const FIELD_CONSULTING_STATUS_NAME = 'Consulting status';
    public const FIELD_SALON_CONSULTING_ID = 'salon_consulting_id';
    public const FIELD_SALON_CONSULTING_NAME = 'salon_consulting_name';
    public const FIELD_SALON_CONSULTING_STATUS_ID = 'salon_consulting_status_id';
    public const FIELD_SALON_CONSULTING_STATUS = 'salon_consulting_status';

    // extra consulting
    public const FIELD_EXTRA_CONSULTING_STATUS_NAME = 'extra_consulting_status';
    public const FIELD_EXTRA_CONSULTING_IN_PROGRESS_DATETIME = 'extra_consulting_in_progress_datetime';
    public const FIELD_EXTRA_CONSULTING_COMPLETE_DATETIME = 'extra_consulting_complete_datetime';

    // manager
    public const FIELD_MANAGER_ID = 'manager_id';
    public const FIELD_MANAGER_NAME = 'manager_name';
    public const FIELD_SALON_MANAGER_ID = 'salon_manager_id';
    public const FIELD_SALON_MANAGER_NAME = 'salon_manager_name';

    // contact fields
    public const FIELD_CONTACT_HAS_BILLING_ACCESS = 'billing_access';
}
