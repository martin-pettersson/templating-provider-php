<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\Collection\ArrayCollection;
use N7e\Collection\CollectionInterface;
use N7e\Configuration\ConfigurationInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use N7e\Templating\TemplateEngineInterface;
use Override;

/**
 * Provides a configured template engine implementation.
 */
final class TemplatingProvider implements ServiceProviderInterface
{
    /**
     * Registered template engine providers.
     *
     * @var \N7e\Collection\CollectionInterface
     */
    private readonly CollectionInterface $templateEngineProviders;

    /**
     * Configured template engine.
     *
     * @var \N7e\Templating\TemplateEngineInterface|null
     */
    private ?TemplateEngineInterface $templateEngine = null;

    /**
     * Create a new service provider instance.
     */
    public function __construct()
    {
        $this->templateEngineProviders = new ArrayCollection([]);
    }

    #[Override]
    public function configure(ContainerBuilderInterface $containerBuilder): void
    {
        $containerBuilder->addFactory('template-engine-providers', fn() => $this->templateEngineProviders)->singleton();
        $containerBuilder->addFactory(TemplateEngineInterface::class, fn() => $this->templateEngine)->singleton();
    }

    #[Override]
    public function load(ContainerInterface $container): void
    {
        /** @var \N7e\Configuration\ConfigurationInterface $configuration */
        $configuration = $container->get(ConfigurationInterface::class);

        /** @var string|null $templateEngine */
        $templateEngine = $configuration->get('templating.templateEngine');

        if (is_null($templateEngine)) {
            return;
        }

        /** @var \N7e\TemplateEngineProviderInterface|null $templateEngineProvider */
        $templateEngineProvider = $this->templateEngineProviders->find(
            static fn($provider) => $provider->canProvideImplementationFor($templateEngine)
        );

        if (is_null($templateEngineProvider)) {
            throw new TemplateEngineProviderNotFoundException($templateEngine);
        }

        $this->templateEngine = $templateEngineProvider->createImplementationUsing($configuration);
    }
}
