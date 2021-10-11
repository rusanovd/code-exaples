<?php

declare(strict_types=1);

namespace More\Amo\Services;

use CSalon;
use More\SalonSettings\Data\SalonInternalSettings;
use More\UserSettings\Services\UserInternalSettingsService;
use Psr\Cache\InvalidArgumentException;

class AmoFieldsMapper
{
    public const ENTITY_TYPE_CONTACT = 'contact';
    public const ENTITY_TYPE_LEAD = 'lead';
    public const ENTITY_TYPE_DEAL = 'deal';

    public const FIELD_ID = 'id';
    public const FIELD_NAME = 'name';
    public const FIELD_CONTAINER_FIELDS = 'fields';
    public const FIELD_LINKED_LEAD_IDS = 'linked_leads_id';
    public const FIELD_MANAGER_ID = 'responsible_user_id';

    /**
     * @var int[]
     */
    private const LEAD_CONSULTING_STATUS_MAP = [
        SalonInternalSettings::CONSULTING_STATUS_WAITING     => 960170, // Ожидает внедрения
        SalonInternalSettings::CONSULTING_STATUS_IN_PROGRESS => 960172, // Идет внедрение
        SalonInternalSettings::CONSULTING_STATUS_PAUSE       => 960174, // Внедрение на паузе
        SalonInternalSettings::CONSULTING_STATUS_COMPLETE    => 960176, // Завершено внедрение
        SalonInternalSettings::CONSULTING_STATUS_MISSING     => 960400, // Без внедрения
        SalonInternalSettings::CONSULTING_STATUS_NO_DEPTH    => 960250, // Без глубины внедрения
    ];

    /**
     * Значения амо статусов для дополнительного внедрения
     * @var int[]
     */
    private const LEAD_EXTRA_CONSULTING_STATUS_MAP = [
        SalonInternalSettings::CONSULTING_STATUS_WAITING     => 965702, // Ожидает внедрения
        SalonInternalSettings::CONSULTING_STATUS_IN_PROGRESS => 965704, // Идет внедрение
        SalonInternalSettings::CONSULTING_STATUS_COMPLETE    => 965708, // Завершено внедрение
        SalonInternalSettings::CONSULTING_STATUS_MISSING     => 965710, // Без внедрения
        SalonInternalSettings::CONSULTING_STATUS_PAUSE       => 965706, // Внедрение на паузе
        SalonInternalSettings::CONSULTING_STATUS_NO_DEPTH    => 965712, // Без глубины внедрения
    ];

    /**
     * @var int[]
     */
    private const LEAD_ACTIVITY_MAP = [
        0 => 964440, // Нет
        1 => 964438, // Да
        2 => 964442, // Пробный
    ];

    /**
     * хранится в onboarding_place_count_dict
     * @var int[]
     */
    private const LEAD_PLACE_COUNT_MAP = [
        1 => 956572, // Один
        2 => 956574, // Несколько
        3 => 956576, // Я — частный мастер
    ];

    /**
     * хранится в onboarding_staff_count_dict
     * @var int[]
     */
    private const LEAD_STUFF_COUNT_MAP = [
        // Для всех, кроме медицины
        1 => 956578, // 1
        2 => 956580, // 2 ‒ 5
        3 => 956582, // 6-10
        4 => 956584, // 11 ‒ 20
        5 => 956586, // 21+

        // Медицина
        6 => 964872, // 2 - 4
        7 => 964874, // 5 - 8
        8 => 964876, // 9 - 12
        9 => 964878, // 13+
    ];

    /**
     * хранится в onboarding_registration_purposes_dict
     * @var int[]
     */
    private const LEAD_PURPOSES_MAP = [
        1  => 946522, // Работа с возвращаемостью
        2  => 946516, // Онлайн-запись
        3  => 946502, // Аналитика и статистика
        4  => 946504, // Ведение склада
        5  => 946530, // Финансовый учет
        6  => 946500, // Автоматические уведомления и рассылки
        7  => 946528, // Сбор отзывов от клиентов
        8  => 946526, // Расчет зарплаты
        9  => 946508, // Внедрение программ лояльности
        10 => 946512, // Клиентская база
    ];

    /**
     * @var int[]
     */
    private const LEAD_TIME_ZONE_MAP = [
        -1  => 924367,
        -2  => 924365,
        -3  => 924363,
        -4  => 924437,
        -5  => 924439,
        -6  => 924441,
        -7  => 924443,
        -8  => 924445,
        -9  => 947180,
        -10 => 947182,
        -11 => 961932,
        -12 => 962744,
        0   => 924369,
        1   => 924371,
        2   => 924373,
        3   => 924375,
        4   => 924377,
        5   => 924379,
        6   => 924381,
        7   => 924383,
        8   => 924385,
        9   => 924387,
        10  => 924389,
        11  => 924391,
        12  => 961934,
        13  => 961936,
        14  => 961938,
        15  => 961940,
        16  => 962012,
    ];

    /**
     * @var int[]
     */
    private const LEAD_BUSINESS_GROUP_MAP = [
        1  => 951904, // Красота
        2  => 951906, // Медицина
        3  => 951908, // Спорт
        4  => 951916, // Авто
        5  => 951910, // Обучение
        6  => 951912, // Досуг и отдых
        7  => 951918, // Розница retail
        8  => 951920, // Другой бизнес,
        9  => 951922, // Неизвестно
        10 => 951914, // Бытовые услуги
    ];

    /**
     * @var int[]
     */
    private const LEAD_BUSINESS_TYPE_MAP = [
        1   => 606465, // Салон красоты
        2   => 606473, // Автомойка
        3   => 907499, // Автосервис
        4   => 606469, // Медицинский центр
        5   => 610551, // Сауна
        6   => 907509, // Фотостудия
        7   => 606491, // Клининг
        8   => 606509, // Другие организации
        9   => 606499, // Юридическая компания
        10  => 606483, // Фитнес-клуб
        11  => 606497, // Зооуслуги
        12  => 606501, // Ресторан
        13  => 606495, // Свадебный салон
        14  => 606471, // Квест
        15  => 936076, // Партнер YCLIENTS
        16  => 907513, // Дизайн интерьера
        17  => 907523, // Салон оптики
        18  => 606467, // Барбершоп
        19  => 936078, // Франчайзи YCLIENTS
        20  => 606477, // Частный мастер
        21  => 606507, // Многопрофильные курсы
        22  => 936080, // Организация мероприятий
        23  => 610555, // Спортивная школа
        24  => 606485, // Школа танцев
        25  => 907507, // SPA
        26  => 936032, // Другой beauty-бизнес
        27  => 606503, // Курсы (красота)
        28  => 606487, // Соляные пещеры
        29  => 907525, // Массажный салон
        30  => 936082, // Неизвестно
        31  => 935970, // Ветеринария
        32  => 935968, // Стоматология
        33  => 907497, // Tattoo
        34  => 935962, // Солярий
        35  => 935964, // Косметология
        36  => 936034, // Прокат инвентаря
        37  => 936014, // Автошкола
        38  => 935972, // Женская консультация
        39  => 935974, // Диагностический центр
        40  => 935986, // Анализы
        41  => 935984, // Поликлиника
        42  => 935982, // Больница
        43  => 935980, // Альтернативная медицина
        44  => 935978, // Психотерапия и психология
        45  => 935958, // Ногтевой сервис
        46  => 936050, // Шиномонтаж
        47  => 935992, // Школа единоборств
        48  => 935960, // Брови и ресницы
        49  => 936030, // Ремонт техники
        50  => 936000, // Yoga
        51  => 936068, // Коворкинг
        52  => 936004, // Детская секция
        53  => 936006, // Языковая школа
        54  => 935998, // Бассейн
        55  => 935996, // Батутный центр
        56  => 935990, // EMS
        57  => 935956, // Детская парикмахерская
        58  => 935994, // Частный тренер
        59  => 936008, // Репетитор
        60  => 936010, // Подготовка к тестированию
        61  => 936028, // Ателье
        62  => 936026, // Химчистка
        63  => 936038, // Авто-ателье
        64  => 936048, // Тюнинг-центр
        65  => 936040, // Техосмотр
        66  => 936042, // Автосалон
        67  => 936052, // Другой автобизнес
        68  => 936018, // Боулинг
        69  => 936020, // Киберспорт
        70  => 936024, // Другие развлечения
        71  => 936016, // Другое обучение
        72  => 936070, // Нотариус
        73  => 936072, // Адвокатское бюро
        74  => 936074, // Бухгалтерия и аудит
        75  => 936036, // Другие услуги
        76  => 936002, // Другой спорт
        77  => 936044, // Детейлинг
        78  => 936046, // Частный механик
        79  => 936056, // Магазин косметики
        80  => 936054, // Мебель и интерьер
        81  => 936064, // Магазин продуктов
        82  => 936062, // Книжный магазин
        83  => 936060, // Торговый центр
        84  => 936058, // Бутик и show-room
        85  => 936012, // Музыкальная школа
        86  => 936066, // Другая розница
        87  => 935988, // Другая медицина
        88  => 936022, // Картинг
        89  => 952558, //Теннис и сквош
        90  => 958552, // Другие услуги (Красота)
        91  => 958530, // Оздоровительный массаж
        92  => 958532, // Частный специалист (Медицина)
        93  => 958542, // Аренда авто
        94  => 958560, // Частный специалист (Авто)
        95  => 958536, // Частные услуги (Спорт)
        96  => 958554, // Частный специалист (Обучение)
        97  => 958556, // Частный специалист (Досуг и отдых)
        98  => 958558, // Частный специалист (Бытовые услуги)
        99  => 958548, // Недвижимость
        100 => 958562, // Частный специалист (Другой бизнес)
        101 => 958528, // Эпиляция
        102 => 285259, // Частный специалист (Розница)
        103 => 966774, // Мед. косметология
    ];

    /**
     * @var int[]
     */
    private const LEAD_REFERRAL_SOURCE_MAP = [
        1 => 962364, // Рекомендация
        2 => 962366, // Реклама в Интернете
        3 => 962368, // Конференция
        4 => 962370, // Знаю давно
        5 => 962372, // Не помню
    ];

    /**
     * @var string[]
     */
    private const LEAD_MODERATION_STATUS_MAP = [
        CSalon::MOD_FAIL          => 'Не отмодерирован',
        CSalon::MOD_WAIT          => 'На модерации',
        CSalon::MOD_OK            => 'Отмодерирован',
        CSalon::MOD_CONDITIONALLY => 'Условно отмодерирован',
    ];

    /**
     * @var int
     */
    private const LEAD_BUSINESS_GROUP_DEFAULT = 951922; // Неизвестно

    /**
     * @var int
     */
    private const LEAD_BUSINESS_TYPE_DEFAULT = 936082; // Неизвестно

    /**
     * @var int
     */
    private const LEAD_TIMEZONE_DEFAULT = 924369; // 0

    private UserInternalSettingsService $userInternalSettingsService;

    public function __construct(UserInternalSettingsService $userInternalSettingsService)
    {
        $this->userInternalSettingsService = $userInternalSettingsService;
    }

    public function getAmoBusinessTypeId(int $yclientsBusinessTypeId): int
    {
        return self::LEAD_BUSINESS_TYPE_MAP[$yclientsBusinessTypeId] ?? self::LEAD_BUSINESS_TYPE_DEFAULT;
    }

    public function getAmoTimezone(int $timezone): int
    {
        return self::LEAD_TIME_ZONE_MAP[$timezone] ?? self::LEAD_TIMEZONE_DEFAULT;
    }

    public function getAmoBusinessGroupId(int $yclientsBusinessGroupId): int
    {
        return self::LEAD_BUSINESS_GROUP_MAP[$yclientsBusinessGroupId] ?? self::LEAD_BUSINESS_GROUP_DEFAULT;
    }

    public function getAmoBusinessPlaceCountId(int $yclientsBusinessPlaceCountId): int
    {
        return self::LEAD_PLACE_COUNT_MAP[$yclientsBusinessPlaceCountId] ?? 0;
    }

    public function getAmoBusinessStuffCountId(int $yclientsBusinessStuffCountId): int
    {
        return self::LEAD_STUFF_COUNT_MAP[$yclientsBusinessStuffCountId] ?? 0;
    }

    /**
     * @param int $yclientsUserId
     * @return int
     * @throws InvalidArgumentException
     */
    public function getAmoConsultingManagerId(int $yclientsUserId): int
    {
        $settings = $this->userInternalSettingsService->findByUserId($yclientsUserId);

        return $settings ? $settings->getAmoConsultingId() : 0;
    }

    /**
     * @param int $yclientsUserId
     * @return int
     * @throws InvalidArgumentException
     */
    public function getAmoExtraConsultingManagerId(int $yclientsUserId): int
    {
        $settings = $this->userInternalSettingsService->findByUserId($yclientsUserId);

        return $settings ? $settings->getAmoExtraConsultingId() : 0;
    }

    public function getAmoConsultingStatusId(int $yclientsStatusId): int
    {
        return self::LEAD_CONSULTING_STATUS_MAP[$yclientsStatusId] ?? 0;
    }

    public function getAmoExtraConsultingStatusId(int $yclientsExtraStatusId): int
    {
        return self::LEAD_EXTRA_CONSULTING_STATUS_MAP[$yclientsExtraStatusId] ?? 0;
    }

    /**
     * @param int $yclientsUserId
     * @return int
     * @throws InvalidArgumentException
     */
    public function getAmoIntegratorManagerId(int $yclientsUserId): int
    {
        $settings = $this->userInternalSettingsService->findByUserId($yclientsUserId);

        return $settings ? $settings->getAmoIntegratorId() : 0;
    }

    /**
     * @param array $yclientsBusinessPurposesIds
     * @return int[]
     */
    public function getAmoBusinessPurposesIds(array $yclientsBusinessPurposesIds): array
    {
        if (empty($yclientsBusinessPurposesIds)) {
            return [];
        }
        $amoBusinessPurposesIds = [];
        foreach ($yclientsBusinessPurposesIds as $yclientsBusinessPurposesId) {
            if (isset(self::LEAD_PURPOSES_MAP[$yclientsBusinessPurposesId])) {
                $amoBusinessPurposesIds[] = (int) self::LEAD_PURPOSES_MAP[$yclientsBusinessPurposesId];
            }
        }

        return $amoBusinessPurposesIds;
    }

    public function getConsultingStatusId(int $amoStatusId): int
    {
        return (int) array_search($amoStatusId, self::LEAD_CONSULTING_STATUS_MAP, true);
    }

    public function getExtraConsultingStatusId(int $amoStatusId): int
    {
        return (int) array_search($amoStatusId, self::LEAD_EXTRA_CONSULTING_STATUS_MAP, true);
    }

    /**
     * @param int $amoManagerId
     * @return int
     * @throws InvalidArgumentException
     */
    public function getConsultingManagerId(int $amoManagerId): int
    {
        $settings = $this->userInternalSettingsService->findByAmoConsultingId($amoManagerId);

        return $settings ? $settings->getUserId() : 0;
    }

    /**
     * @param int $amoManagerId
     * @return int
     * @throws InvalidArgumentException
     */
    public function getIntegratorManagerId(int $amoManagerId): int
    {
        $settings = $this->userInternalSettingsService->findByAmoIntegratorId($amoManagerId);

        return $settings ? $settings->getUserId() : 0;
    }

    /**
     * @param int $amoManagerId
     * @return int
     * @throws InvalidArgumentException
     */
    public function getExtraConsultingManagerId(int $amoManagerId): int
    {
        $settings = $this->userInternalSettingsService->findByAmoExtraConsultingId($amoManagerId);

        return $settings ? $settings->getUserId() : 0;
    }

    public function getModerationStatus(int $statusId): string
    {
        return self::LEAD_MODERATION_STATUS_MAP[$statusId] ?? '';
    }

    public function getAmoReferralSourceId(int $yclientsReferralSourceId): int
    {
        return self::LEAD_REFERRAL_SOURCE_MAP[$yclientsReferralSourceId] ?? 0;
    }

    public function getAmoActivityId(int $activityId): int
    {
        return self::LEAD_ACTIVITY_MAP[$activityId] ?? 0;
    }
}
