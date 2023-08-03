<?php

declare(strict_types=1);

namespace DotTest\Mail;

use Laminas\Mail\Transport\Smtp;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

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
                    'extends' => null,

                    /**
                     * the mail transport to use
                     * can be any class implementing Laminas\Mail\Transport\TransportInterface
                     *
                     * for standard mail transports, you can use these aliases
                     * - sendmail => Laminas\Mail\Transport\Sendmail
                     * - smtp => Laminas\Mail\Transport\Smtp
                     * - file => Laminas\Mail\Transport\File
                     * - in_memory => Laminas\Mail\Transport\InMemory
                     *
                     * defaults to sendmail
                     **/
                    'transport' => Smtp::class,

                    // Uncomment the below line if you want to save a copy of all sent emails to a certain IMAP folder
                    // Valid only if the Transport is SMTP
                    // 'save_sent_message_folder' => ['INBOX.Sent'],

                    // Uncomment the below line if you want to save a copy of all sent emails to a certain IMAP folder
                    // Valid only if the Transport is SMTP
                    'save_sent_message_folder' => ['INBOX.Sent'],
                    'message_options'          => [
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

                    //options that will be used only if Laminas\Mail\Transport\Smtp adapter is used
                    'smtp_options' => [
                        'host'              => '',
                        'port'              => 587,
                        'connection_class'  => 'login',
                        'connection_config' => [

                            //the smtp authentication identity
                            //'username' => '',

                            //the smtp authentication credential
                            //'password' => '',
                            'ssl' => 'tls',
                        ],
                    ],

                    //file options that will be used only if the adapter is Laminas\Mail\Transport\File
                    'file_options' => [
                        'path' => $this->fileSystem->url() . '/data/mail/output',

                        //a callable that will get the Laminas\Mail\Transport\File object as an argument and should
                        // return the filename
                        //if null is used, and empty callable will be used
                        //'callback' => null,
                    ],

                    //listeners to register with the mail service, for mail events
                    'event_listeners' => [
                        //[
                        //'type' => 'service or class name',
                        //'priority' => 1
                        //],
                    ],
                ],
                // option to log the SENT emails
                'log' => [
                    'sent' => $this->fileSystem->url() . '/log/mail/sent.log',
                ],

                /**
                 * You can define other mail services here, with the same structure as the default block
                 * you can even extend from the default block, and overwrite only the differences
                 */
            ],
        ];
    }
}
