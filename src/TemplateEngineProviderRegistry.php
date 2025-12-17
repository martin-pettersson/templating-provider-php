<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use Override;

/**
 * Implementation of {@see \N7e\TemplateEngineProviderRegistryInterface}.
 */
final class TemplateEngineProviderRegistry implements TemplateEngineProviderRegistryInterface
{
    /**
     * Registered providers.
     *
     * @var \N7e\TemplateEngineProviderInterface[]
     */
    private array $providers = [];

    #[Override]
    public function register(TemplateEngineProviderInterface $provider): void
    {
        array_unshift($this->providers, $provider);
    }

    #[Override]
    public function providerFor(string $templateEngine): TemplateEngineProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->canProvideImplementationFor($templateEngine)) {
                return $provider;
            }
        }

        throw new TemplateEngineProviderNotFoundException($templateEngine);
    }
}
