<?php

declare(strict_types=1);

namespace Dot\Mail\Factory;

use DirectoryIterator;
use Dot\Mail\Event\MailEventListenerAwareInterface;
use Dot\Mail\Event\MailEventListenerInterface;
use Dot\Mail\Exception\InvalidArgumentException;
use Dot\Mail\Exception\RuntimeException;
use Dot\Mail\Options\MailOptions;
use Dot\Mail\Service\LogServiceInterface;
use Dot\Mail\Service\MailService;
use Dot\Mail\Service\MailServiceInterface;
use FilesystemIterator;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\Smtp;
use Laminas\Mail\Transport\TransportInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function explode;
use function gettype;
use function is_array;
use function is_dir;
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

        $logService = $container->get(LogServiceInterface::class);
        $message    = $this->createMessage();
        $transport  = $this->createTransport($container);

        $mailService = new MailService($logService, $message, $transport, $this->mailOptions);

        //set subject
        $mailService->setSubject($this->mailOptions->getMessageOptions()->getSubject());

        $body = $this->mailOptions->getMessageOptions()->getBody();
        $mailService->setBody($body->getContent(), $body->getCharset());

        //attach files
        $files = $this->mailOptions->getMessageOptions()->getAttachments()->getFiles();
        $mailService->addAttachments($files);

        //attach files from dir
        $dir = $this->mailOptions->getMessageOptions()->getAttachments()->getDir();

        if ($dir['iterate'] === true && is_string($dir['path']) && is_dir($dir['path'])) {
            $files = $dir['recursive'] === true
                ? new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $dir['path'],
                        FilesystemIterator::SKIP_DOTS
                    ),
                    RecursiveIteratorIterator::CHILD_FIRST
                )
                : new DirectoryIterator($dir['path']);

            foreach ($files as $fileInfo) {
                if ($fileInfo->isDir()) {
                    continue;
                }

                $mailService->addAttachment($fileInfo->getPathname());
            }
        }

        $this->attachMailListeners($mailService, $container);
        return $mailService;
    }

    protected function createMessage(): Message
    {
        $options = $this->mailOptions->getMessageOptions();
        $message = new Message();

        $from = $options->getFrom();
        if (! empty($from)) {
            $message->setFrom($from, $options->getFromName());
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

        return $message;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createTransport(ContainerInterface $container): TransportInterface
    {
        $adapter = $this->mailOptions->getTransport();
        if ($adapter instanceof TransportInterface) {
            return $this->setupTransportConfig($adapter);
        }

        //check is adapter is a service
        if ($container->has($adapter)) {
            $transport = $container->get($adapter);
            if ($transport instanceof TransportInterface) {
                return $this->setupTransportConfig($transport);
            } else {
                throw new InvalidArgumentException(
                    'Provided mail_adapter service does not return a ' . TransportInterface::class . ' instance'
                );
            }
        }

        //check is the adapter is one of Laminas's default adapters
        if (is_subclass_of($adapter, TransportInterface::class)) {
            return $this->setupTransportConfig(new $adapter());
        }

        //the adapter is not valid - throw exception
        throw new InvalidArgumentException(
            sprintf(
                'mail_adapter must be an instance of %s or string, "%s" provided',
                TransportInterface::class,
                gettype($adapter)
            )
        );
    }

    protected function setupTransportConfig(TransportInterface $transport): TransportInterface
    {
        if ($transport instanceof Smtp) {
            $transport->setOptions($this->mailOptions->getSmtpOptions());
        } elseif ($transport instanceof File) {
            $transport->setOptions($this->mailOptions->getFileOptions());
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
                'Mail event listener must be an instance of `%s`, but `%s was given`',
                MailEventListenerInterface::class,
                is_object($listener) ? $listener::class : gettype($listener)
            ));
        }

        return $listener;
    }
}
