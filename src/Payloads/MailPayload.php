<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Mail\SentMessage;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use ReflectionClass;
use Symfony\Component\Mime\Part\DataPart;

class MailPayload extends Payload
{
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
            $bodyProperty->setAccessible(true);

            /** @var \Symfony\Component\Mime\Part\File $body */
            $body = $bodyProperty->getValue($dataPart);
            $path = $body->getPath();

            $filename = $dataPart->getFilename();

            $dataPartsData[] = [
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
