<?php

declare(strict_types=1);

namespace More\Integration\Intercom\Services;

use Application\Http\Response\ResponseFactory;
use CSalon;
use Infrastructure\Metrics\Facade\Metrics;
use More\User\Interfaces\UserInterface;

class IntercomTrackerService
{
    private IntercomConfig $intercomConfig;
    private IntercomCompanyOptionsService $intercomCompanyOptionsService;
    private IntercomContactOptionsService $intercomContactOptionsService;
    private IntercomFieldsService $intercomFieldsService;

    /**
     * IntercomTrackerService constructor.
     * @param IntercomConfig $intercomConfig
     * @param IntercomCompanyOptionsService $intercomCompanyOptionsService
     * @param IntercomContactOptionsService $intercomContactOptionsService
     * @param IntercomFieldsService $intercomFieldsService
     */
    public function __construct(
        IntercomConfig $intercomConfig,
        IntercomCompanyOptionsService $intercomCompanyOptionsService,
        IntercomContactOptionsService $intercomContactOptionsService,
        IntercomFieldsService $intercomFieldsService
    ) {
        $this->intercomConfig = $intercomConfig;
        $this->intercomCompanyOptionsService = $intercomCompanyOptionsService;
        $this->intercomContactOptionsService = $intercomContactOptionsService;
        $this->intercomFieldsService = $intercomFieldsService;
    }

    /**
     * @return string
     */
    public function getAppId(): string
    {
        return $this->intercomConfig->getAppID();
    }

    /**
     * @param UserInterface|null $user
     * @param CSalon|null $salon
     * @return bool
     */
    private function isNeedTracker(?UserInterface $user, ?CSalon $salon): bool
    {
        if ($salon === null || $user === null) {
            return false;
        }

        if (! $user->getId() || ! $salon->getId()) {
            return false;
        }

        return $this->intercomConfig->isEnabled();
    }

    /**
     * Включает ТОЛЬКО поля из users / salons
     *
     * @param UserInterface|null $user
     * @param CSalon|null $salon
     * @return array
     */
    private function getIntercomTrackerSettings(?UserInterface $user, ?CSalon $salon): array
    {
        if (! $this->isNeedTracker($user, $salon)) {
            return [];
        }

        $hash = hash_hmac('sha256', (string) $user->getId(), $this->intercomConfig->getAppHash());

        $options = array_merge(
            $this->intercomContactOptionsService->getOptionsIdentification($user),
            $this->intercomContactOptionsService->getOptionsUtm($user),
            $this->intercomContactOptionsService->getOptionsLetters($user),
            $this->intercomContactOptionsService->getOptionsConsulting($salon),
            $this->intercomContactOptionsService->getOptionsManager($salon),
            $this->intercomContactOptionsService->getOptionsLocation($salon),
            [
                IntercomFieldsMapper::FIELD_NAME             => htmlspecialchars_decode($user->getFirstName()),
                IntercomFieldsMapper::FIELD_CREATED_AT       => $this->intercomFieldsService->getTimeStamp($user->getCreateDate()),
                IntercomFieldsMapper::FIELD_APP_ID           => $this->intercomConfig->getAppID(),
                IntercomFieldsMapper::FIELD_APP_LAUNCHER     => '.' . $this->intercomConfig::APP_LAUNCHER_SELECTOR_CLASS,
                IntercomFieldsMapper::FIELD_PHONE            => $user->getPhoneString(),
                IntercomFieldsMapper::FIELD_ZENDESK_ID       => $user->getZendeskId(),
                IntercomFieldsMapper::FIELD_USER_HASH        => $hash,
            ]
        );

        $options[IntercomFieldsMapper::FIELD_COMPANY] = array_merge(
            $this->intercomCompanyOptionsService->getOptionsSalonActivity($salon),
            $this->intercomCompanyOptionsService->getOptionsUtm($salon),
            [
                IntercomFieldsMapper::FIELD_ID                       => $salon->getId(),
                IntercomFieldsMapper::FIELD_NAME                     => $salon->getTitle(),
                IntercomFieldsMapper::FIELD_CREATED_AT               => $this->intercomFieldsService->getTimeStamp($salon->getCreationDate()),
                IntercomFieldsMapper::FIELD_BALANCE_ABS              => $salon->getBalance(),
                IntercomFieldsMapper::FIELD_WEBSITE                  => $salon->getSite(),
                IntercomFieldsMapper::FIELD_PHONE                    => count($salon->getContactPhones()) ? $salon->getContactPhones()[0] : '',
                IntercomFieldsMapper::FIELD_EMAIL                    => $salon->getEmail(),
                IntercomFieldsMapper::FIELD_LAST_RECORD_DATE         => $this->intercomFieldsService->getTimeStamp($salon->getLastRecordCreateDate()),
                IntercomFieldsMapper::FIELD_AVG_SPEND                => $salon->getAvgSpends(),
                IntercomFieldsMapper::FIELD_PROMO                    => $salon->getPromo(),
                IntercomFieldsMapper::FIELD_ZENDESK_ID               => $salon->getZendeskId(),
                IntercomFieldsMapper::FIELD_MANAGER_ID               => $salon->getManagerId(),
                IntercomFieldsMapper::FIELD_CITY_ID                  => $salon->getCityId(),
            ]
        );

        return $options;
    }

    public function getIntercomTrackerScript(?UserInterface $user, ?CSalon $salon): string
    {
        if (! $intercomSettings = $this->getIntercomTrackerSettings($user, $salon)) {
            return '';
        }

        $url = 'https://widget.' . $this->intercomConfig->getHost() . '/widget/' . $this->intercomConfig->getAppID();
        $json = json_encode($intercomSettings, ResponseFactory::DEFAULT_JSON_FLAGS);

        Metrics::increment(IntercomMetric::createRequestSuccessMetric(IntercomMetric::METRIC_REQUEST_CHAT));

        return '<script>
          window.intercomSettings = ' . $json . ';
           (function() {
            var w = window;
            var ic = w.Intercom;
            if (typeof ic === "function") {
                ic("reattach_activator");
                ic("update", intercomSettings);
            } else {
                var d = document;
                var i = function() {
                    i.c(arguments)
                };
                i.q = [];
                i.c = function(args) {
                    i.q.push(args)
                };
                w.Intercom = i;

                function l() {
                    var s = d.createElement("script");
                    s.type = "text/javascript";
                    s.async = true;
                    s.src = "' . $url . '";
                    var x = d.getElementsByTagName("script")[0];
                    x.parentNode.insertBefore(s, x);
                }
                if (w.attachEvent) {
                    w.attachEvent("onload", l);
                } else {
                    w.addEventListener("load", l, false);
                }
            }
        })()
        </script>';
    }
}
