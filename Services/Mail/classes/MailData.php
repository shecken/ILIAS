<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

class MailData
{
    protected string $to;
    protected string $cc;
    protected string $bcc;
    protected string $subject;
    protected string $message;
    protected array $attachments;
    protected ?int $internal_mail_id;
    protected bool $use_placeholder;

    public function __construct(
        string $to,
        string $cc,
        string $bcc,
        string $subject,
        string $message,
        array $attachments,
        bool $use_placeholder,
        ?int $internal_mail_id = null
    ) {
        $this->to = $to;
        $this->cc = $cc;
        $this->bcc = $bcc;
        $this->subject = $subject;
        $this->message = $message;
        $this->attachments = $attachments;
        $this->use_placeholder = $use_placeholder;
        $this->internal_mail_id = $internal_mail_id;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getCc(): string
    {
        return $this->cc;
    }

    public function getBcc(): string
    {
        return $this->bcc;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function getInternalMailId(): ?int
    {
        return $this->internal_mail_id;
    }

    public function isUsePlaceholder(): bool
    {
        return $this->use_placeholder;
    }

    public function withInternalMailId(int $internal_mail_id): MailData
    {
        $clone = clone $this;
        $clone->internal_mail_id = $internal_mail_id;
        return $clone;
    }
}
