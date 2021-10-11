<?php

namespace More\Amo\ServiceProvider;

use Infrastructure\ServiceProvider\AbstractServiceProvider;
use More\Amo\OldApi\AmoOldApiClient;
use More\Amo\Services\AmoClient;
use More\Amo\Services\AmoConfig;

class AmoServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string[]
     */
    protected array $provides = [
        AmoConfig::class,
        AmoClient::class,
        AmoOldApiClient::class,
    ];

    public function register()
    {
        $container = $this->getContainer();

        $container->share(AmoConfig::class, static function () use ($container) {
            return new AmoConfig(
                $container->getParameter('integrations.amo.host'),
                $container->getParameter('integrations.amo.domain'),
                $container->getParameter('integrations.amo.login'),
                $container->getParameter('integrations.amo.hash'),
                $container->getParameter('integrations.amo.enabled'),
                $container->getParameter('integrations.evotor.partner_id')
            );
        });

        $container->share(AmoClient::class, static function () use ($container) {
            return new AmoClient(
                $container->getParameter('integrations.amo.domain'),
                $container->getParameter('integrations.amo.login'),
                $container->getParameter('integrations.amo.hash'),
                $container->getParameter('integrations.amo.enabled'),
            );
        });

        $container->share(AmoOldApiClient::class, static function () use ($container) {
            return new AmoOldApiClient(
                $container->getParameter('integrations.amo.host'),
                $container->getParameter('integrations.amo.domain'),
                $container->getParameter('integrations.amo.login'),
                $container->getParameter('integrations.amo.hash'),
                $container->getParameter('integrations.amo.enabled')
            );
        });
    }
}
