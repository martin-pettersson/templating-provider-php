<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\Configuration\ConfigurationInterface;
use N7e\DependencyInjection\ContainerBuilderInterface;
use N7e\DependencyInjection\ContainerInterface;
use N7e\Templating\TemplateEngineInterface;
use Override;
use RuntimeException;

/**
 * Provides a configured template engine implementation.
 */
final class TemplatingProvider implements ServiceProviderInterface
{
    /**
     * Configured template engine.
     *
     * @var \N7e\Templating\TemplateEngineInterface|null
     */
    private ?TemplateEngineInterface $templateEngine = null;

    #[Override]
    public function configure(ContainerBuilderInterface $containerBuilder): void
    {
        $containerBuilder->addClass(TemplateEngineProviderRegistry::class)
            ->singleton()
            ->alias(TemplateEngineProviderRegistryInterface::class);
        $containerBuilder->addFactory(TemplateEngineInterface::class, function () {
            if (is_null($this->templateEngine)) {
                throw new RuntimeException('Cannot use template engine before the templating provider\'s load phase');
            }

            return $this->templateEngine;
        })
            ->singleton();
    }

    #[Override]
    public function load(ContainerInterface $container): void
    {
        /** @var \N7e\TemplateEngineProviderRegistryInterface $templateEngineProviders */
        $templateEngineProviders = $container->get(TemplateEngineProviderRegistryInterface::class);

        /** @var \N7e\Configuration\ConfigurationInterface $configuration */
        $configuration = $container->get(ConfigurationInterface::class);

        $this->templateEngine = $templateEngineProviders
            ->providerFor($configuration->get('templating.engine', ''))
            ->createImplementationUsing($configuration);
    }
}
