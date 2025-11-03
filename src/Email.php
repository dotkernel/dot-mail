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
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Symfony\Component\Mime\Part\Multipart\RelatedPart;
use Symfony\Component\Mime\Part\TextPart;

use function array_filter;
use function array_merge;
use function array_unique;
use function array_values;
use function is_array;
use function is_string;
use function preg_match_all;
use function sprintf;
use function sscanf;
use function str_replace;

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

    private ?string $htmlCharset      = null;
    private array $attachments        = [];
    private ?AbstractPart $cachedBody = null;

    /**
     * @return $this
     */
    public function subject(string $subject): static
    {
        $this->setHeaderBody('Text', 'Subject', $subject);

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->getHeaders()->getHeaderBody('Subject');
    }

    /**
     * @return $this
     */
    public function date(DateTimeInterface $dateTime): static
    {
        $this->setHeaderBody('Date', 'Date', $dateTime);

        return $this;
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->getHeaders()->getHeaderBody('Date');
    }

    /**
     * @return $this
     */
    public function returnPath(Address|string $address): static
    {
        $this->setHeaderBody('Path', 'Return-Path', Address::create($address));

        return $this;
    }

    public function getReturnPath(): ?Address
    {
        return $this->getHeaders()->getHeaderBody('Return-Path');
    }

    public function setSender(Address|string $addresses, mixed $name = null): static
    {
        return $this->setHeaderBody('Mailbox', 'Sender', new Address($addresses, $name ?? ''));
    }

    public function getSender(): ?Address
    {
        return $this->getHeaders()->getHeaderBody('Sender');
    }

    public function addFrom(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('From', $updatedAddresses);
    }

    public function setFrom(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('From', $updatedAddresses);
    }

    public function getFrom(): array
    {
        return $this->getHeaders()->getHeaderBody('From') ?: [];
    }

    public function addReplyTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('Reply-To', $updatedAddresses);
    }

    public function setReplyTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('Reply-To', $updatedAddresses);
    }

    public function getReplyTo(): array
    {
        return $this->getHeaders()->getHeaderBody('Reply-To') ?: [];
    }

    public function addTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        $this->addListAddressHeaderBody('To', $updatedAddresses);

        return $this;
    }

    public function setTo(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('To', $updatedAddresses);
    }

    public function getTo(): array
    {
        return $this->getHeaders()->getHeaderBody('To') ?: [];
    }

    public function addCc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('Cc', $updatedAddresses);
    }

    public function setCc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('Cc', $updatedAddresses);
    }

    public function getCc(): array
    {
        return $this->getHeaders()->getHeaderBody('Cc') ?: [];
    }

    public function addBcc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->addListAddressHeaderBody('Bcc', $updatedAddresses);
    }

    public function setBcc(array|Address|string $addresses, ?string $name = null): static
    {
        $updatedAddresses = $this->updateAddresses($addresses, $name ?? '');
        return $this->setListAddressHeaderBody('Bcc', $updatedAddresses);
    }

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
        $headerValue = $this->getHeaders()->getHeaderBody('X-Priority');

        if ($headerValue === null) {
            return self::PRIORITY_NORMAL;
        }

        [$priority] = sscanf($headerValue, '%[1-5]');

        return $priority !== null ? (int) $priority : self::PRIORITY_NORMAL;
    }

    public function text(string $body, string $charset = 'utf-8'): static
    {
        $this->cachedBody  = null;
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
        $this->cachedBody  = null;
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
        if (null === $this->text && null === $this->html && ! $this->attachments) {
            throw new LogicException('A message must have a text or an HTML part or attachments.');
        }
    }

    private function generateBody(): AbstractPart
    {
        if (null !== $this->cachedBody) {
            return $this->cachedBody;
        }

        $this->ensureBodyValid();

        [$htmlPart, $otherParts, $relatedParts] = $this->prepareParts();

        $part = null === $this->text ? null : new TextPart($this->text, $this->textCharset);
        if (null !== $htmlPart) {
            if (null !== $part) {
                $part = new AlternativePart($part, $htmlPart);
            } else {
                $part = $htmlPart;
            }
        }

        if ($relatedParts) {
            $part = new RelatedPart($part, ...$relatedParts);
        }

        if ($otherParts) {
            if ($part) {
                $part = new MixedPart($part, ...$otherParts);
            } else {
                $part = new MixedPart(...$otherParts);
            }
        }

        return $part ?? new TextPart($this->text, $this->textCharset);
    }

    private function prepareParts(): array
    {
        $names    = [];
        $htmlPart = null;
        $html     = $this->html;
        if (null !== $html) {
            $htmlPart = new TextPart($html, $this->htmlCharset, 'html');
            $html     = $htmlPart->getBody();

            $regexes    = [
                '<img\s+[^>]*src\s*=\s*(?:([\'"])cid:(.+?)\\1|cid:([^>\s]+))',
                '<\w+\s+[^>]*background\s*=\s*(?:([\'"])cid:(.+?)\\1|cid:([^>\s]+))',
            ];
            $tmpMatches = [];
            foreach ($regexes as $regex) {
                preg_match_all('/' . $regex . '/i', $html, $tmpMatches);
                $names = array_merge($names, $tmpMatches[2], $tmpMatches[3]);
            }
            $names = array_filter(array_unique($names));
        }

        $otherParts = $relatedParts = [];
        foreach ($this->attachments as $part) {
            foreach ($names as $name) {
                if ($name !== $part->getName() && (! $part->hasContentId() || $name !== $part->getContentId())) {
                    continue;
                }
                if (isset($relatedParts[$name])) {
                    continue 2;
                }

                if ($name !== $part->getContentId()) {
                    $html = str_replace('cid:' . $name, 'cid:' . $part->getContentId(), $html, $count);
                }
                $relatedParts[$name] = $part;
                $part->setName($part->getContentId())->asInline();

                continue 2;
            }

            $otherParts[] = $part;
        }
        if (null !== $htmlPart) {
            $htmlPart = new TextPart($html, $this->htmlCharset, 'html');
        }

        return [$htmlPart, $otherParts, array_values($relatedParts)];
    }

    private function setHeaderBody(string $type, string $name, mixed $body): static
    {
        $this->getHeaders()->setHeaderBody($type, $name, $body);

        return $this;
    }

    private function addListAddressHeaderBody(string $name, array $addresses): static
    {
        $header = $this->getHeaders()->get($name);

        if ($header instanceof MailboxListHeader) {
            $header->addAddresses(Address::createArray($addresses));

            return $this;
        }

        return $this->setListAddressHeaderBody($name, $addresses);
    }

    private function setListAddressHeaderBody(string $name, array $addresses): static
    {
        $addresses = Address::createArray($addresses);
        $headers   = $this->getHeaders();
        $header    = $headers->get($name);

        if ($header instanceof MailboxListHeader) {
            $header->setAddresses($addresses);
        } else {
            $headers->addMailboxListHeader($name, $addresses);
        }

        return $this;
    }

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

    private function createAddresses(array $addresses, ?string $name = null): array
    {
        $createdAddresses = [];
        foreach ($addresses as $address) {
            $createdAddresses[] = new Address($address, $name ?? '');
        }

        return $createdAddresses;
    }
}
