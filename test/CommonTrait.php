<?php

declare(strict_types=1);

namespace DotTest\Mail;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

trait CommonTrait
{
    protected array $config;
    protected vfsStreamDirectory $fileSystem;

    public function setup(): void
    {
        $this->fileSystem = vfsStream::setup('root', 0644, [
            'log'  => ['mail'],
            'data' => [
                'mail' => [
                    'attachments' => [
                        'testPdfAttachment.pdf' => 'pdf content',
                        'testXlsAttachment.xls' => 'xls content',
                    ],
                    'output'      => [],
                ],
            ],
        ]);

        $this->config = $this->generateConfig();
    }

    public function getConfig(): array
    {
        return $this->getConfig();
    }

    public function getFileSystem(): vfsStreamDirectory
    {
        return $this->fileSystem;
    }

    private function generateConfig(): array
    {
        return [
            'dot_mail' => [
                'default' => [
                    /**
                     * the mail transport to use
                     * can be any class implementing Symfony\Component\Mailer\Transport\TransportInterface
                     *
                     * for standard mail transports, you can use these aliases
                     * - sendmail => Symfony\Component\Mailer\Transport\SendmailTransport
                     * - smtp     => Symfony\Component\Mailer\Transport\Smtp\SmtpTransport
                     *
                     * defaults to sendmail
                     **/
                    'transport'       => SmtpTransport::class,
                    'message_options' => [
                        'from'          => '',
                        'from_name'     => '',
                        'reply_to'      => '',
                        'reply_to_name' => '',
                        'to'            => [],
                        'cc'            => [],
                        'bcc'           => [],
                        'subject'       => '',
                        'body'          => [
                            'content' => '',
                            'charset' => 'utf-8',
                        ],
                        'attachments'   => [
                            'files' => [],
                            'dir'   => [
                                'iterate'   => false,
                                'path'      => $this->fileSystem->url() . '/data/mail/attachments',
                                'recursive' => false,
                            ],
                        ],
                    ],
                    //options that will be used only if Symfony\Component\Mailer\Transport\Smtp\SmtpTransport
                    // adapter is used
                    'smtp_options' => [
                        'host'              => 'testHost',
                        'port'              => 587,
                        'connection_config' => [
                            //the smtp authentication identity
                            'username' => 'test',
                            //the smtp authentication credential
                            'password' => 'testPassword',
                        ],
                    ],
                ],
                'test'    => 'string test',

                // option to log the SENT emails
                'log' => [
                    'sent' => $this->fileSystem->url() . '/log/mail/sent.log',
                ],
            ],
        ];
    }
}
