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
use N7e\DependencyInjection\DependencyDefinitionInterface;
use N7e\Templating\TemplateEngineInterface;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(TemplatingProvider::class)]
class TemplatingProviderTest extends TestCase
{
    private TemplatingProvider $provider;
    private MockObject $containerBuilderMock;
    private MockObject $containerMock;
    private MockObject $registryMock;
    private MockObject $configurationMock;

    #[Before]
    public function setUp(): void
    {
        $this->provider = new TemplatingProvider();
        $this->containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->registryMock = $this->getMockBuilder(TemplateEngineProviderRegistryInterface::class)->getMock();
        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();

        $this->containerMock->method('get')->willReturnCallback(fn($identifier) => match ($identifier) {
            TemplateEngineProviderRegistryInterface::class => $this->registryMock,
            ConfigurationInterface::class => $this->configurationMock,
            default => throw new RuntimeException("No mock found for: {$identifier}"),
        });
        $this->configurationMock->method('get')->willReturn('');
    }

    #[Test]
    public function shouldRegisterNecessaryTemplatingComponents(): void
    {
        $this->containerBuilderMock
            ->expects($this->once())
            ->method('addClass')
            ->with(TemplateEngineProviderRegistry::class);
        $this->containerBuilderMock
            ->expects($this->once())
            ->method('addFactory')
            ->with(TemplateEngineInterface::class, $this->isCallable());

        $this->provider->configure($this->containerBuilderMock);
    }

    #[Test]
    public function shouldThrowExceptionIfAccessingTemplateEngineBeforeLoadPhase(): void
    {
        $this->expectException(RuntimeException::class);

        $templateEngineFactory = null;

        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(function ($identifier, $factory) use (&$templateEngineFactory) {
                $templateEngineFactory = $factory;

                return $this->getMockBuilder(DependencyDefinitionInterface::class)->getMock();
            });

        $this->provider->configure($this->containerBuilderMock);

        $templateEngineFactory();
    }

    #[Test]
    public function shouldNotThrowExceptionIfAccessingTemplateEngineAfterLoadPhase(): void
    {
        $this->expectNotToPerformAssertions();

        $templateEngineFactory = null;

        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(function ($identifier, $factory) use (&$templateEngineFactory) {
                $templateEngineFactory = $factory;

                return $this->getMockBuilder(DependencyDefinitionInterface::class)->getMock();
            });

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);

        $templateEngineFactory();
    }

    #[Test]
    public function shouldCreateTemplateEngineFromProviderRegistry(): void
    {
        $templateEngineMock = $this->getMockBuilder(TemplateEngineInterface::class)->getMock();
        $templateEngineProviderMock = $this->getMockBuilder(TemplateEngineProviderInterface::class)->getMock();
        $templateEngineFactory = null;

        $this->containerBuilderMock
            ->method('addFactory')
            ->willReturnCallback(function ($identifier, $factory) use (&$templateEngineFactory) {
                $templateEngineFactory = $factory;

                return $this->getMockBuilder(DependencyDefinitionInterface::class)->getMock();
            });
        $this->registryMock->method('providerFor')->willReturn($templateEngineProviderMock);
        $templateEngineProviderMock->method('createImplementationUsing')->willReturn($templateEngineMock);

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);

        $this->assertSame($templateEngineMock, $templateEngineFactory());
    }
}
