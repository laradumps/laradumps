<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\SentMessage;
use LaraDumps\LaraDumpsCore\Payloads\{Label, Payload, Screen};
use ReflectionClass;
use Symfony\Component\Mime\Part\{DataPart, File};

class MailPayload extends Payload
{
    public const MAX_ATTACH_BINARY_FILE_IN_MB = 25;

    protected array $mailProperties = [];

    public function __construct(
        SentMessage $sentMessage,
        array $details,
        string $messageId,
        private string $screen = 'mail',
        private string $label = ''
    ) {
        $sentMessage = $sentMessage->getOriginalMessage();

        $html = strval($sentMessage->getHtmlBody()); // @phpstan-ignore-line

        /** @var array $attachments */
        $attachments = $sentMessage->getAttachments(); // @phpstan-ignore-line

        $dataPartsData = [];

        /** @var DataPart $dataPart */
        foreach ($attachments as $dataPart) {
            $reflection = new ReflectionClass($dataPart);

            $reflectionParent = $reflection->getParentClass();
            $bodyProperty = $reflectionParent->getProperty('body'); // @phpstan-ignore-line

            /** @var string|File $body */
            $body = $bodyProperty->getValue($dataPart);

            if (is_string($body)) {
                $body = base64_encode($body);
                $path = null;
                $size = strlen($body);

                if ($size > (self::MAX_ATTACH_BINARY_FILE_IN_MB * 1024 * 1024)) {
                    $body = null;
                }
            } else {
                $path = $body->getPath();
            }

            $filename = $dataPart->getFilename();

            $dataPartsData[] = [
                'body' => is_string($body) ? $body : null,
                'path' => $path,
                'filename' => $filename,
            ];
        }

        $this->mailProperties = [
            'messageId' => $messageId,
            'html' => $html,
            'details' => $details,
            'attachments' => $dataPartsData,
            'headers' => $sentMessage->getHeaders()->toArray(), // @phpstan-ignore-line
        ];
    }

    public function type(): string
    {
        return 'mail';
    }

    public function content(): array
    {
        return $this->mailProperties;
    }

    public function toScreen(): array|Screen
    {
        return new Screen($this->screen);
    }

    public function withLabel(): array|Label
    {
        return new Label($this->label);
    }
}
