<?php

declare(strict_types=1);

namespace More\Integration\Intercom\ServiceProvider;

use Infrastructure\ServiceProvider\AbstractServiceProvider;
use Intercom\IntercomClient;
use More\Integration\Intercom\Services\IntercomConfig;

class IntercomServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string[]
     */
    protected array $provides = [
        IntercomConfig::class,
        IntercomClient::class,
    ];

    public function register()
    {
        $container = $this->getContainer();

        $container->share(IntercomConfig::class, static function () use ($container) {
            return new IntercomConfig(
                $container->getParameter('integrations.intercom.host'),
                $container->getParameter('integrations.intercom.enabled'),
                $container->getParameter('integrations.intercom.app.id'),
                $container->getParameter('integrations.intercom.app.hash'),
                $container->getParameter('integrations.intercom.api.version')
            );
        });

        $container->share(IntercomClient::class, static function () use ($container) {
            return new IntercomClient(
                $container->getParameter('integrations.intercom.api.token'),
                null,
                ['Intercom-Version' => $container->getParameter('integrations.intercom.api.version')]
            );
        });
    }
}
