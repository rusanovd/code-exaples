<?php

namespace More\Amo\Data;

use More\Amo\Data\AmoResponse\AmoEntityContainer;

class AmoLead
{
    // lead not specific category fields
    public const LEAD_FIELD_ID_LAST_RECORD_CREATE_DATE = 478680;
    public const LEAD_FIELD_ID_IS_VIP = 488490; // Ключевой клиент
    public const LEAD_FIELD_ID_TIMEZONE = 460209;
    public const LEAD_FIELD_ID_WEBSITE = 490572; // Веб сайт
    public const LEAD_FIELD_ID_SMS_AGGREGATOR_NAME = 490562; // Агрегатор sms

    // onboarding
    public const LEAD_FIELD_ID_PLACE_COUNT = 486710;
    public const LEAD_FIELD_ID_STAFF_COUNT_1 = 486712;
    public const LEAD_FIELD_ID_PURPOSE = 479292;
    public const LEAD_FIELD_ID_TIME_TO_CALL = 563056; // Время для демонстрации
    public const LEAD_FIELD_ID_TIME_TO_CALL_DATE = 563368; // Дата звонка
    public const LEAD_FIELD_ID_TIME_TO_CALL_TIME = 563370; // Время звонка
    public const LEAD_FIELD_ID_REFERRAL_SOURCE = 491736; // Откуда вы узнали о нас?
    public const LEAD_FIELD_ID_IS_PROVIDE_SERVICES_BY_MYSELF = 491738; // Оказываю услуги самостоятельно
    public const LEAD_FIELD_ID_LAST_COMPLETE_STEP = 562978; // Шаг онбординга

    // links/flags to outers services
    public const LEAD_FIELD_ID_SALON_ID = 247743;
    public const LEAD_FIELD_ID_LINK_TO_SALON = 247683;
    public const LEAD_FIELD_ID_ADMIN_APP = 488106;
    public const LEAD_FIELD_ID_IS_REGISTERED_VIA_EVOTOR = 477982;
    public const LEAD_FIELD_ID_ROISTAT_VISIT = 474566;

    // location
    public const LEAD_FIELD_ID_CITY_TITLE = 285439;
    public const LEAD_FIELD_ID_COUNTRY_TITLE = 484564;
    public const LEAD_FIELD_ID_COUNTRY_GROUP = 562530;

    // promo
    public const LEAD_FIELD_ID_PROMO_ID = 488100;
    public const LEAD_FIELD_ID_PROMO_CODE = 486434;

    // utm
    public const LEAD_FIELD_ID_UTM_SOURCE = 474084;
    public const LEAD_FIELD_ID_UTM_MEDIUM = 474086;
    public const LEAD_FIELD_ID_UTM_CAMPAIGN = 474088;
    public const LEAD_FIELD_ID_UTM_CONTENT = 474090;
    public const LEAD_FIELD_ID_UTM_TERM = 474098;

    // plan
    public const LEAD_FIELD_ID_PLAN_NAME = 247687;
    public const LEAD_FIELD_ID_STAFF_COUNT_2 = 247745;
    public const LEAD_FIELD_ID_TARIFF_COMMENT = 247689; // Комментарий к тарифу
    public const LEAD_FIELD_ID_MODERATION_STATUS = 490564; // Статус модерации
    public const LEAD_FIELD_ID_TARIFF_IS_FREEZE = 490566; // Заморозка
    public const LEAD_FIELD_ID_TARIFF_DISCOUNT = 490574; // Скидка
    public const LEAD_FIELD_ID_SALON_IS_DELETED = 490568; // Филиал удален
    public const LEAD_FIELD_ID_IS_INDIVIDUAL = 488158; // Частный мастер

    // plan options
    public const LEAD_FIELD_ID_KKM_PAID = 490560; // Модуь ККМ
    public const LEAD_FIELD_ID_NOTIFICATIONS_PAID = 490558; // Модуль уведомлений

    // reactivation
    public const LEAD_FIELD_ID_LAST_START_REACTIVATION_DATE = 477512;
    public const LEAD_PIPELINE_ID_REACTIVATION = 1006690; // Воронка "Реактивация"
    public const LEAD_PIPELINE_ID_ARCHIVE = 1060123; // Воронка "Архив"
    public const LEAD_STATUS_ID_REACTIVATION = 23269039; // Статус "Реактивация (неразобранное)"

    // activity
    public const LEAD_FIELD_ID_ACTIVITY = 562572; // Активен
    public const LEAD_FIELD_ID_LAST_ACTIVATION_DATE = 477538; // Дата последней активации
    public const LEAD_FIELD_ID_LAST_DEACTIVATION_DATE = 477540; // Дата последней деактивации
    public const LEAD_FIELD_ID_DEACTIVATION_DATE = 462459; // Дата оконч. лиц-ии
    public const LEAD_FIELD_ID_SUBSCRIBED_AT = 477536; // Дата подключения

    // business
    public const LEAD_BUSINESS_GROUP_FIELD_ID = 483742; // Id поля для yc группы бизнеса / амо - тип бизнеса
    public const LEAD_BUSINESS_TYPE_FIELD_ID = 285259;  // Id поля для yc типа бизнеса / амо - сфера
    public const LEAD_BUSINESS_DESCRIPTION_FIELD_ID = 483760; // Id поля для описания / амо - категория бизнеса

    // consulting
    public const LEAD_FIELD_ID_INTEGRATOR_MANAGER_ID = 493042; // Куратор для интеграторов
    public const LEAD_FIELD_ID_CONSULTING_STATUS_ID = 490704; // Статус внедрения
    public const LEAD_FIELD_ID_CONSULTING_MANAGER_ID = 471416; // Менеджер внедрения
    public const LEAD_FIELD_ID_CONSULTING_START = 490466;
    public const LEAD_FIELD_ID_CONSULTING_END = 490468;

    // extra consulting
    public const LEAD_FIELD_ID_EXTRA_CONSULTING_STATUS_ID = 564296; // Статус дополнительного внедрения
    public const LEAD_FIELD_ID_EXTRA_CONSULTING_START = 564298; // Дата начала дополнительного внедрения
    public const LEAD_FIELD_ID_EXTRA_CONSULTING_END = 564300; // Дата окончания дополнительного внедрения
    public const LEAD_FIELD_ID_EXTRA_CONSULTING_MANAGER_ID = 566338; // Менеджер дополнительного внедрения

    // system inner fields
    public const KEY_CHANGE_TITLE = 'title';
    public const KEY_CHANGE_COUNTRY = 'country_id';
    public const KEY_CHANGE_CITY = 'city_id';
    public const KEY_CHANGE_BUSINESS_TYPE_ID = 'biz_type_id';
    public const KEY_CHANGE_TIMEZONE = 'timezone';
    public const KEY_CHANGE_SITE = 'site';
    public const KEY_CHANGE_PHONES = 'phones';

    // system outer fields
    public const LEAD_NAME_AUTO_GENERATED = 'Автосделка: ';

    private int $id;
    private int $salonId;
    private int $pipelineId;
    private int $responsibleUserId;
    private int $statusId;

    public static function createFromAmoEntityContainer(AmoEntityContainer $amoEntityResponse): AmoLead
    {
        return (new self())
            ->setId($amoEntityResponse->getId())
            ->setSalonId((int) $amoEntityResponse->getCustomFieldValue(self::LEAD_FIELD_ID_SALON_ID, true))
            ->setPipelineId((int) $amoEntityResponse->getFieldByKey('pipeline_id'))
            ->setResponsibleUserId($amoEntityResponse->getResponsibleUserId())
            ->setStatusId((int) $amoEntityResponse->getFieldByKey('status_id'));
    }

    protected function setId(int $id): AmoLead
    {
        $this->id = $id;

        return $this;
    }

    protected function setSalonId(int $salonId): AmoLead
    {
        $this->salonId = $salonId;

        return $this;
    }

    public function getSalonId(): int
    {
        return $this->salonId;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPipelineId(): int
    {
        return $this->pipelineId;
    }

    public function setPipelineId(int $pipelineId): AmoLead
    {
        $this->pipelineId = $pipelineId;

        return $this;
    }

    public function getResponsibleUserId(): int
    {
        return $this->responsibleUserId;
    }

    public function setResponsibleUserId(int $responsibleUserId): AmoLead
    {
        $this->responsibleUserId = $responsibleUserId;

        return $this;
    }

    public function getStatusId(): int
    {
        return $this->statusId;
    }

    public function setStatusId(int $statusId): AmoLead
    {
        $this->statusId = $statusId;

        return $this;
    }

    public function isPipelineReactivation(): bool
    {
        return $this->getPipelineId() === self::LEAD_PIPELINE_ID_REACTIVATION;
    }

    public function isPipelineArchive(): bool
    {
        return $this->getPipelineId() === self::LEAD_PIPELINE_ID_ARCHIVE;
    }

    public function isStatusReactivation(): bool
    {
        return $this->getStatusId() === self::LEAD_STATUS_ID_REACTIVATION;
    }
}
