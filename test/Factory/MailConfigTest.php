<?php

declare(strict_types=1);

namespace DotTest\Mail\Factory;

use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Factory\MailConfig;
use DotTest\Mail\Fixture\InMemoryContainer;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function array_keys;

class MailConfigTest extends TestCase
{
    public function testServiceNameIsReadFromTheKey(): void
    {
        $this->assertSame('default', MailConfig::serviceName('dot-mail.service.default', 'service'));
        $this->assertSame('newsletter', MailConfig::serviceName('dot-mail.options.newsletter', 'options'));
    }

    public function testMalformedKeysAreRejected(): void
    {
        $keys = [
            'dot-mail',
            'dot-mail.service',
            'dot-mail.service.',
            'dot-mail.service.default.extra',
            'other.service.default',
            'dot-mail.options.default',
        ];

        foreach ($keys as $key) {
            try {
                MailConfig::serviceName($key, 'service');
                $this->fail('Expected InvalidArgumentException for ' . $key);
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('"dot-mail.service.<name>"', $e->getMessage(), $key);
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testServicesComeOnlyFromTheServicesKey(): void
    {
        $container = new InMemoryContainer([
            'config' => [
                'dot_mail' => [
                    'log'      => ['sent' => '/tmp/sent.log'],
                    'services' => ['log' => ['transport' => 'sendmail'], 'other' => []],
                ],
            ],
        ]);

        $this->assertSame(['log', 'other'], array_keys(MailConfig::services($container)));
        $this->assertSame(['transport' => 'sendmail'], MailConfig::service($container, 'log'));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testOldConfigurationLayoutIsRejected(): void
    {
        $container = new InMemoryContainer([
            'config' => [
                'dot_mail' => [
                    'default' => ['transport' => 'sendmail'],
                    'log'     => ['sent' => '/tmp/sent.log'],
                ],
            ],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mail service "default" must be configured under "dot_mail.services"');
        MailConfig::services($container);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testConfigurationWithOnlyLogHasNoServices(): void
    {
        $container = new InMemoryContainer(['config' => ['dot_mail' => ['log' => ['sent' => '/tmp/sent.log']]]]);

        $this->assertSame([], MailConfig::services($container));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testMissingConfigurationHasNoServices(): void
    {
        $this->assertSame([], MailConfig::services(new InMemoryContainer()));
        $this->assertSame([], MailConfig::services(new InMemoryContainer(['config' => ['other' => []]])));
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function testUnconfiguredOrInvalidServiceIsRejected(): void
    {
        $container = new InMemoryContainer(['config' => ['dot_mail' => ['services' => ['text' => 'not an array']]]]);

        foreach (['missing', 'text'] as $name) {
            try {
                MailConfig::service($container, $name);
                $this->fail('Expected InvalidArgumentException for ' . $name);
            } catch (InvalidArgumentException $e) {
                $this->assertSame(
                    'Mail service "' . $name . '" is not configured under "dot_mail.services"',
                    $e->getMessage()
                );
            }
        }
    }
}
