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
use N7e\Templating\TemplateEngineInterface;

/**
 * Has the ability to provide a template engine implementation.
 */
interface TemplateEngineProviderInterface
{
    /**
     * Determine whether this provider can provide an implementation for a given template engine.
     *
     * @param string $templateEngine Arbitrary template engine identifier.
     * @return bool True if the provider can provide an implementation for a given template engine.
     */
    public function canProvideImplementationFor(string $templateEngine): bool;

    /**
     * Create a template engine implementation using given configuration.
     *
     * @param \N7e\Configuration\ConfigurationInterface $configuration Arbitrary configuration.
     * @return \N7e\Templating\TemplateEngineInterface Configured template engine instance.
     */
    public function createImplementationUsing(ConfigurationInterface $configuration): TemplateEngineInterface;
}
