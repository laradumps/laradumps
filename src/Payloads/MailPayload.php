<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\SentMessage;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use ReflectionClass;
use Symfony\Component\Mime\Part\{DataPart, File};

class MailPayload extends Payload
{
    const MAX_ATTACH_BINARY_FILE_IN_MB = 25;

    protected array $mailProperties = [];

    public function __construct(SentMessage $sentMessage, array $details, string $messageId)
    {
        $sentMessage = $sentMessage->getOriginalMessage();

        /** @phpstan-ignore-next-line */
        $html = strval($sentMessage->getHtmlBody());
        /** @phpstan-ignore-next-line */
        $attachments = $sentMessage->getAttachments();

        $dataPartsData = [];

        /** @var DataPart $dataPart */
        foreach ($attachments as $dataPart) {
            $reflection = new ReflectionClass($dataPart);

            $reflectionParent = $reflection->getParentClass();
            /** @phpstan-ignore-next-line */
            $bodyProperty = $reflectionParent->getProperty('body');

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
                'body'     => is_string($body) ? $body : null,
                'path'     => $path,
                'filename' => $filename,
            ];
        }

        $this->mailProperties = [
            'messageId'   => $messageId,
            'html'        => $html,
            'details'     => $details,
            'attachments' => $dataPartsData,
            /** @phpstan-ignore-next-line */
            'headers' => $sentMessage->getHeaders()->toArray(),
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
}
