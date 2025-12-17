<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

/**
 * Represents a set of registered template engine providers.
 */
interface TemplateEngineProviderRegistryInterface
{
    /**
     * Register given template engine provider.
     *
     * @param \N7e\TemplateEngineProviderInterface $provider Arbitrary template engine provider.
     */
    public function register(TemplateEngineProviderInterface $provider): void;

    /**
     * Produce an appropriate template engine provider for a given template engine identifier.
     *
     * @param string $templateEngine Arbitrary template engine identifier.
     * @return \N7e\TemplateEngineProviderInterface Appropriate template engine provider.
     */
    public function providerFor(string $templateEngine): TemplateEngineProviderInterface;
}
