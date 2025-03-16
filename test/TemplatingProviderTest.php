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
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(TemplatingProvider::class)]
class TemplatingProviderTest extends TestCase
{
    private TemplatingProvider $provider;
    private MockObject $containerBuilderMock;
    private MockObject $containerMock;
    private MockObject $configurationMock;

    #[Before]
    public function setUp(): void
    {
        $this->containerBuilderMock = $this->getMockBuilder(ContainerBuilderInterface::class)->getMock();
        $this->containerMock = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $this->configurationMock = $this->getMockBuilder(ConfigurationInterface::class)->getMock();
        $this->provider = new TemplatingProvider();

        $this->containerMock->method('get')
            ->with(ConfigurationInterface::class)
            ->willReturn($this->configurationMock);
    }

    private function capture(&$destination): Callback
    {
        return $this->callback(static function ($source) use (&$destination) {
            $destination = $source;

            return true;
        });
    }

    #[Test]
    public function shouldNotConfigureTemplateEngineIfInappropriate(): void
    {
        $this->containerBuilderMock
            ->expects($this->exactly(2))
            ->method('addFactory')
            ->with($this->anything(), $this->capture($factoryCallback));

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);

        $this->assertNull($factoryCallback());
    }

    #[Test]
    public function shouldConfigureKnownTemplateEngine(): void
    {
        $templateEngine = 'php';
        $templateEngineMock = $this->getMockBuilder(TemplateEngineInterface::class)->getMock();
        $templateEngineProviderMock = $this->getMockBuilder(TemplateEngineProviderInterface::class)->getMock();

        $this->containerBuilderMock
            ->expects($this->exactly(2))
            ->method('addFactory')
            ->with($this->anything(), $this->capture($factoryCallback));
        $templateEngineProviderMock
            ->expects($this->once())
            ->method('canProvideImplementationFor')
            ->with($templateEngine)
            ->willReturn(true);
        $templateEngineProviderMock
            ->method('createImplementationUsing')
            ->willReturn($templateEngineMock);
        $this->configurationMock
            ->method('get')
            ->with('templating.templateEngine')
            ->willReturn($templateEngine);

        $this->provider->configure($this->containerBuilderMock);

        $providersProperty = (new ReflectionClass(TemplatingProvider::class))->getProperty('templateEngineProviders');
        $providersProperty->setAccessible(true);
        $providersProperty->getValue($this->provider)->add($templateEngineProviderMock);

        $this->provider->load($this->containerMock);

        $this->assertSame($templateEngineMock, $factoryCallback());
    }

    #[Test]
    public function shouldThrowIfUnknownTemplateEngine(): void
    {
        $this->expectException(TemplateEngineProviderNotFoundException::class);
        $this->configurationMock
            ->method('get')
            ->with('templating.templateEngine')
            ->willReturn('php');

        $this->provider->configure($this->containerBuilderMock);
        $this->provider->load($this->containerMock);
    }
}
