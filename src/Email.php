<?php

declare(strict_types=1);

namespace Dot\Mail;

use DateTimeImmutable;
use DateTimeInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Exception\LogicException;
use Symfony\Component\Mime\Header\MailboxListHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\Part\AbstractPart;
use Symfony\Component\Mime\Part\Multipart\AlternativePart;
use Symfony\Component\Mime\Part\TextPart;

use function is_array;
use function is_string;
use function preg_match;
use function sprintf;

class Email extends Message
{
    public const PRIORITY_HIGHEST = 1;
    public const PRIORITY_HIGH    = 2;
    public const PRIORITY_NORMAL  = 3;
    public const PRIORITY_LOW     = 4;
    public const PRIORITY_LOWEST  = 5;

    private const PRIORITY_MAP = [
        self::PRIORITY_HIGHEST => 'Highest',
        self::PRIORITY_HIGH    => 'High',
        self::PRIORITY_NORMAL  => 'Normal',
        self::PRIORITY_LOW     => 'Low',
        self::PRIORITY_LOWEST  => 'Lowest',
    ];

    private ?string $text        = null;
    private ?string $textCharset = null;
    private ?string $html        = null;

    private ?string $htmlCharset = null;

    public function subject(string $subject): static
    {
        return $this->setHeaderBody('Text', 'Subject', $subject);
    }

    public function getSubject(): ?string
    {
        return $this->getHeaders()->getHeaderBody('Subject');
    }

    public function date(DateTimeInterface $dateTime): static
    {
        return $this->setHeaderBody('Date', 'Date', $dateTime);
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->getHeaders()->getHeaderBody('Date');
    }

    public function returnPath(Address|string $address): static
    {
        return $this->setHeaderBody('Path', 'Return-Path', Address::create($address));
    }

    public function getReturnPath(): ?Address
    {
        return $this->getHeaders()->getHeaderBody('Return-Path');
    }

    public function setSender(Address|string $address, ?string $name = null): static
    {
        $sender = $address instanceof Address ? $address : new Address($address, $name ?? '');

        return $this->setHeaderBody('Mailbox', 'Sender', $sender);
    }

    public function getSender(): ?Address
    {
        return $this->getHeaders()->getHeaderBody('Sender');
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function addFrom(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('From', $updatedAddresses);
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function setFrom(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('From', $updatedAddresses);
    }

    /**
     * @return Address[]
     */
    public function getFrom(): array
    {
        return $this->getHeaders()->getHeaderBody('From') ?: [];
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function addReplyTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('Reply-To', $updatedAddresses);
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function setReplyTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('Reply-To', $updatedAddresses);
    }

    /**
     * @return Address[]
     */
    public function getReplyTo(): array
    {
        return $this->getHeaders()->getHeaderBody('Reply-To') ?: [];
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function addTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        $this->addListAddressHeaderBody('To', $updatedAddresses);

        return $this;
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function setTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('To', $updatedAddresses);
    }

    /**
     * @return Address[]
     */
    public function getTo(): array
    {
        return $this->getHeaders()->getHeaderBody('To') ?: [];
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function addCc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('Cc', $updatedAddresses);
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function setCc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('Cc', $updatedAddresses);
    }

    /**
     * @return Address[]
     */
    public function getCc(): array
    {
        return $this->getHeaders()->getHeaderBody('Cc') ?: [];
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function addBcc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('Bcc', $updatedAddresses);
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     */
    public function setBcc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('Bcc', $updatedAddresses);
    }

    /**
     * @return Address[]
     */
    public function getBcc(): array
    {
        return $this->getHeaders()->getHeaderBody('Bcc') ?: [];
    }

    public function priority(int $priority): static
    {
        if ($priority > 5) {
            $priority = 5;
        } elseif ($priority < 1) {
            $priority = 1;
        }

        return $this->setHeaderBody('Text', 'X-Priority', sprintf('%d (%s)', $priority, self::PRIORITY_MAP[$priority]));
    }

    public function getPriority(): int
    {
        $header = (string) $this->getHeaders()->getHeaderBody('X-Priority');

        return preg_match('/^([1-5])/', $header, $matches) === 1 ? (int) $matches[1] : self::PRIORITY_NORMAL;
    }

    public function text(string $body, string $charset = 'utf-8'): static
    {
        $this->text        = $body;
        $this->textCharset = $charset;

        return $this;
    }

    public function setEncoding(string $encoding): static
    {
        $this->htmlCharset = $encoding;

        return $this;
    }

    public function getEncoding(): ?string
    {
        return $this->htmlCharset;
    }

    public function getTextBody(): ?string
    {
        return $this->text;
    }

    public function getTextCharset(): ?string
    {
        return $this->textCharset;
    }

    public function html(string $body, string $charset = 'utf-8'): static
    {
        $this->html        = $body;
        $this->htmlCharset = $charset;

        return $this;
    }

    public function getHtmlBody(): ?string
    {
        return $this->html;
    }

    public function getHtmlCharset(): ?string
    {
        return $this->htmlCharset;
    }

    public function getBody(): AbstractPart
    {
        if (null !== $body = parent::getBody()) {
            return $body;
        }

        return $this->generateBody();
    }

    public function ensureValidity(): void
    {
        $this->ensureBodyValid();

        if ('1' === $this->getHeaders()->getHeaderBody('X-Unsent')) {
            throw new LogicException('Cannot send messages marked as "draft".');
        }

        parent::ensureValidity();
    }

    private function ensureBodyValid(): void
    {
        if (null === $this->text && null === $this->html && null === parent::getBody()) {
            throw new LogicException('A message must have a text or an HTML part.');
        }
    }

    private function generateBody(): AbstractPart
    {
        $this->ensureBodyValid();

        $part = null === $this->text ? null : new TextPart($this->text, $this->textCharset);
        if (null !== $this->html) {
            $htmlPart = new TextPart($this->html, $this->htmlCharset, 'html');
            $part     = null !== $part ? new AlternativePart($part, $htmlPart) : $htmlPart;
        }

        return $part ?? new TextPart((string) $this->text, $this->textCharset);
    }

    private function setHeaderBody(string $type, string $name, mixed $body): static
    {
        $this->getHeaders()->setHeaderBody($type, $name, $body);

        return $this;
    }

    /**
     * @param Address[] $addresses
     */
    private function addListAddressHeaderBody(string $name, array $addresses): static
    {
        $header = $this->getHeaders()->get($name);
        if (! $header instanceof MailboxListHeader) {
            return $this->setListAddressHeaderBody($name, $addresses);
        }
        $header->addAddresses(Address::createArray($addresses));

        return $this;
    }

    /**
     * @param Address[] $addresses
     */
    private function setListAddressHeaderBody(string $name, array $addresses): static
    {
        $addresses = Address::createArray($addresses);
        $headers   = $this->getHeaders();
        if (($header = $headers->get($name)) instanceof MailboxListHeader) {
            $header->setAddresses($addresses);
        } else {
            $headers->addMailboxListHeader($name, $addresses);
        }

        return $this;
    }

    /**
     * @param array<Address|string>|Address|string $addresses
     * @return Address[]
     */
    private function updateAddresses(array|Address|string $addresses, ?string $name = null): array
    {
        if (is_array($addresses)) {
            return $this->createAddresses($addresses, $name);
        }

        if (is_string($addresses)) {
            $address[] = new Address($addresses, $name ?? '');
            return $address;
        }

        return [$addresses];
    }

    /**
     * @param array<Address|string> $addresses
     * @return Address[]
     */
    private function createAddresses(array $addresses, ?string $name = null): array
    {
        $createdAddresses = [];
        foreach ($addresses as $address) {
            $createdAddresses[] = $address instanceof Address
                ? $address
                : new Address($address, $name ?? '');
        }

        return $createdAddresses;
    }
}
