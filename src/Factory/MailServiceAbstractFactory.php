<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use Dot\Mail\Email;
use Dot\Mail\Event\MailEventListenerAwareInterface;
use Dot\Mail\Event\MailEventListenerInterface;
use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Exception\RuntimeException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailService;
use Dot\Mail\Service\MailServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use Symfony\Component\Mailer\Transport\TransportInterface;

use function explode;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function is_subclass_of;
use function sprintf;

class MailServiceAbstractFactory extends AbstractMailFactory
{
    public const SPECIFIC_PART = 'service';

    protected MailOptions $mailOptions;

    /**
     * @param string $requestedName
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null
    ): MailServiceInterface {
        $specificServiceName = explode('.', $requestedName)[2];

        $this->mailOptions = $container->get(
            sprintf(
                '%s.%s.%s',
                self::DOT_MAIL_PART,
                MailOptionsAbstractFactory::SPECIFIC_PART,
                $specificServiceName
            )
        );

        $logService  = $container->get(LogServiceInterface::class);
        $message     = $this->createMessage();
        $transport   = $this->createTransport($container);
        $attachments = $this->mailOptions->getMessageOptions()->getAttachments()->getFiles();

        $mailService = new MailService($logService, $message, $transport, $this->mailOptions, $attachments);

        $this->attachMailListeners($mailService, $container);
        return $mailService;
    }

    protected function createMessage(): Email
    {
        $options = $this->mailOptions->getMessageOptions();
        $message = new Email();

        $from = $options->getFrom();
        if (! empty($from)) {
            $message->addFrom($from, $options->getFromName());
        }

        $replyTo = $options->getReplyTo();
        if (! empty($replyTo)) {
            $message->setReplyTo($replyTo, $options->getReplyToName());
        }

        $to = $options->getTo();
        if (! empty($to)) {
            $message->setTo($to);
        }

        $cc = $options->getCc();
        if (! empty($cc)) {
            $message->setCc($cc);
        }

        $bcc = $options->getBcc();
        if (! empty($bcc)) {
            $message->setBcc($bcc);
        }

        $message->subject($options->getSubject());

        $body = $options->getBody();
        if ($body->getContent() !== '') {
            $message->html($body->getContent(), $body->getCharset());
        }

        return $message;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createTransport(ContainerInterface $container): TransportInterface
    {
        $adapter = $this->mailOptions->getTransport();

        //a transport object provided by the user is used as it is
        if ($adapter instanceof TransportInterface) {
            return $adapter;
        }

        //check is adapter is a service
        if ($container->has($adapter)) {
            $transport = $container->get($adapter);
            if ($transport instanceof TransportInterface) {
                return $transport;
            }

            throw new InvalidArgumentException(
                'Provided transport service does not return a ' . TransportInterface::class . ' instance'
            );
        }

        //the esmtp alias is configured from the smtp options
        if ($adapter === EsmtpTransport::class) {
            return $this->createEsmtpTransport();
        }

        //check is the adapter is Symfony default adapter
        if (is_subclass_of($adapter, TransportInterface::class)) {
            return new $adapter();
        }

        //the adapter is not valid - throw exception
        throw new InvalidArgumentException(
            sprintf(
                '"transport" must be a known alias, a container service or a class implementing %s; "%s" provided',
                TransportInterface::class,
                $adapter
            )
        );
    }

    protected function createEsmtpTransport(): EsmtpTransport
    {
        $smtp       = $this->mailOptions->getSmtpOptions();
        $connection = $smtp->getConnectionConfig();
        $tls        = $connection['tls'] ?? null;

        //null leaves the encryption to the transport (automatic), false disables it
        $transport = new EsmtpTransport($smtp->getHost(), $smtp->getPort(), $tls === false ? false : null);
        $transport->setAutoTls($tls !== false);

        $username = (string) ($connection['username'] ?? '');
        if ($username !== '') {
            $transport->setUsername($username);
        }

        $password = (string) ($connection['password'] ?? '');
        if ($password !== '') {
            $transport->setPassword($password);
        }

        $name = $smtp->getName();
        if ($name !== null) {
            $transport->setLocalDomain($name);
        }

        $timeLimit = $smtp->getConnectionTimeLimit();
        $stream    = $transport->getStream();
        if ($timeLimit !== null && $stream instanceof SocketStream) {
            $stream->setTimeout((float) $timeLimit);
        }

        return $transport;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function attachMailListeners(
        MailEventListenerAwareInterface $service,
        ContainerInterface $container
    ): void {
        $listeners = $this->mailOptions->getEventListeners();
        foreach ($listeners as $listener) {
            if (is_array($listener)) {
                $type     = $listener['type'] ?? '';
                $priority = $listener['priority'] ?? 1;

                $listener = $this->getListenerObject($container, $type);
                $service->attachListener($listener, $priority);
            } elseif (is_string($listener)) {
                $type     = $listener;
                $priority = 1;

                $listener = $this->getListenerObject($container, $type);
                $service->attachListener($listener, $priority);
            }
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getListenerObject(ContainerInterface $container, string $type): MailEventListenerInterface
    {
        $listener = null;
        if ($container->has($type)) {
            $listener = $container->get($type);
        }

        if (! $listener instanceof MailEventListenerInterface) {
            throw new RuntimeException(sprintf(
                'Mail event listener "%s" must be an instance of `%s`, but `%s` was given',
                $type,
                MailEventListenerInterface::class,
                is_object($listener) ? $listener::class : gettype($listener)
            ));
        }

        return $listener;
    }
}
