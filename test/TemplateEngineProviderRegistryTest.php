<?php

/*
 * Copyright (c) 2025 Martin Pettersson
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace N7e;

use N7e\Templating\TemplateEngineInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateEngineProviderRegistry::class)]
class TemplateEngineProviderRegistryTest extends TestCase
{
    private TemplateEngineProviderRegistry $registry;

    #[Before]
    public function setUp(): void
    {
        $this->registry = new TemplateEngineProviderRegistry();
    }

    #[Test]
    public function shouldThrowExceptionIfNoProvidersAreRegistered(): void
    {
        $this->expectException(TemplateEngineProviderNotFoundException::class);

        $this->registry->providerFor('');
    }

    #[Test]
    public function shouldThrowExceptionIfNoAppropriateProviderIsFound(): void
    {
        $this->expectException(TemplateEngineProviderNotFoundException::class);

        $providerMock = $this->getMockBuilder(TemplateEngineProviderInterface::class)->getMock();
        $providerMock->method('canProvideImplementationFor')->willReturn(false);

        $this->registry->register($providerMock);
        $this->registry->providerFor('');
    }

    #[Test]
    public function shouldReturnResultFromLastRegisteredProvider(): void
    {
        $firstTemplateEngine = $this->getMockBuilder(TemplateEngineInterface::class)->getMock();
        $lastTemplateEngine = $this->getMockBuilder(TemplateEngineInterface::class)->getMock();
        $firstProvider = $this->getMockBuilder(TemplateEngineProviderInterface::class)->getMock();
        $lastProvider = $this->getMockBuilder(TemplateEngineProviderInterface::class)->getMock();

        $firstProvider->method('canProvideImplementationFor')->willReturn(true);
        $firstProvider->method('createImplementationUsing')->willReturn($firstTemplateEngine);
        $lastProvider->method('canProvideImplementationFor')->willReturn(true);
        $lastProvider->method('createImplementationUsing')->willReturn($lastTemplateEngine);

        $this->registry->register($firstProvider);
        $this->registry->register($lastProvider);

        $this->assertSame($lastProvider, $this->registry->providerFor(''));
    }
}
