<?php

declare(strict_types=1);

use Faerber\PdfToZpl\Logger\LoggerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Monolog\Logger;

final class LoggerFactoryTest extends TestCase {
    public function testCreateColoredLoggerReturnsLoggerInterface(): void {
        $logger = LoggerFactory::createColoredLogger();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateColoredLoggerWithCustomName(): void {
        $logger = LoggerFactory::createColoredLogger('custom-logger');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateColoredLoggerCanLogMessages(): void {
        $logger = LoggerFactory::createColoredLogger();

        // Should not throw exceptions when logging
        $this->expectNotToPerformAssertions();
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->warning('Warning message');
        $logger->error('Error message');
    }

    public function testCreateColoredLoggerWithContext(): void {
        $logger = LoggerFactory::createColoredLogger();

        // Should not throw exceptions when logging with context
        $this->expectNotToPerformAssertions();
        $logger->info('Message with context', ['key' => 'value', 'number' => 42]);
    }

    public function testCreateEchoLoggerReturnsLoggerInterface(): void {
        $logger = LoggerFactory::createEchoLogger();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateEchoLoggerWithCustomName(): void {
        $logger = LoggerFactory::createEchoLogger('custom-logger');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateEchoLoggerCanLogMessages(): void {
        $logger = LoggerFactory::createEchoLogger();

        // Should not throw exceptions when logging
        $this->expectNotToPerformAssertions();
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->warning('Warning message');
        $logger->error('Error message');
    }

    public function testCreateEchoLoggerWithContext(): void {
        $logger = LoggerFactory::createEchoLogger();

        // Should not throw exceptions when logging with context
        $this->expectNotToPerformAssertions();
        $logger->info('Message with context', ['key' => 'value']);
    }

    public function testCreateVoidLoggerReturnsLoggerInterface(): void {
        $logger = LoggerFactory::createVoidLogger();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateVoidLoggerWithCustomName(): void {
        $logger = LoggerFactory::createVoidLogger('custom-logger');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateVoidLoggerCanLogWithoutErrors(): void {
        $logger = LoggerFactory::createVoidLogger();

        // Should not throw exceptions when logging
        $this->expectNotToPerformAssertions();
        $logger->debug('Debug message');
        $logger->info('Info message');
        $logger->warning('Warning message');
        $logger->error('Error message');
    }

    public function testCreateWithColoredType(): void {
        $logger = LoggerFactory::create('colored');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateWithEchoType(): void {
        $logger = LoggerFactory::create('echo');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateWithVoidType(): void {
        $logger = LoggerFactory::create('void');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateWithDefaultType(): void {
        $logger = LoggerFactory::create();

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateWithInvalidTypeFallsBackToVoid(): void {
        $logger = LoggerFactory::create('invalid-type');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateWithCustomName(): void {
        $logger = LoggerFactory::create('echo', 'my-custom-logger');

        $this->assertInstanceOf(LoggerInterface::class, $logger);
        $this->assertInstanceOf(Logger::class, $logger);
    }

    public function testCreateReturnsCorrectLoggerType(): void {
        $coloredLogger = LoggerFactory::create('colored');
        $echoLogger = LoggerFactory::create('echo');
        $voidLogger = LoggerFactory::create('void');

        // All should be Logger instances but different configurations
        $this->assertInstanceOf(Logger::class, $coloredLogger);
        $this->assertInstanceOf(Logger::class, $echoLogger);
        $this->assertInstanceOf(Logger::class, $voidLogger);

        // They should be different instances
        $this->assertNotSame($coloredLogger, $echoLogger);
        $this->assertNotSame($coloredLogger, $voidLogger);
        $this->assertNotSame($echoLogger, $voidLogger);
    }

    public function testAllLoggerTypesCanLogAllLevels(): void {
        $loggers = [
            LoggerFactory::createColoredLogger(),
            LoggerFactory::createEchoLogger(),
            LoggerFactory::createVoidLogger(),
        ];

        // Should not throw exceptions for any logger type
        $this->expectNotToPerformAssertions();

        foreach ($loggers as $logger) {
            $logger->emergency('Emergency message');
            $logger->alert('Alert message');
            $logger->critical('Critical message');
            $logger->error('Error message');
            $logger->warning('Warning message');
            $logger->notice('Notice message');
            $logger->info('Info message');
            $logger->debug('Debug message');
        }
    }

    public function testFactoryMethodsAreStatic(): void {
        $reflectionClass = new ReflectionClass(LoggerFactory::class);

        $createColoredMethod = $reflectionClass->getMethod('createColoredLogger');
        $createEchoMethod = $reflectionClass->getMethod('createEchoLogger');
        $createVoidMethod = $reflectionClass->getMethod('createVoidLogger');
        $createMethod = $reflectionClass->getMethod('create');

        $this->assertTrue($createColoredMethod->isStatic());
        $this->assertTrue($createEchoMethod->isStatic());
        $this->assertTrue($createVoidMethod->isStatic());
        $this->assertTrue($createMethod->isStatic());
    }
}
