<?php

namespace LaraDumps\LaraDumps\Payloads;

use Illuminate\Notifications\Events\NotificationSent;
use LaraDumps\LaraDumpsCore\Payloads\Payload;
use LaraDumps\LaraDumpsCore\Support\Dumper;
use ReflectionClass;
use Symfony\Component\Mime\Part\DataPart;

class NotificationPayload extends Payload
{
    protected array $notificationProperties = [];

    public function __construct(NotificationSent $notificationSent)
    {
        /** @var \Illuminate\Mail\SentMessage $response */
        $response = $notificationSent->response;

        $sentMessage = $response->getOriginalMessage();

        $reflection = new ReflectionClass($sentMessage);

        $htmlProperty = $reflection->getProperty('html');
        $htmlProperty->setAccessible(true);
        $this->notificationProperties['html'] = strval($htmlProperty->getValue($sentMessage));

        $attachmentsProperty = $reflection->getProperty('attachments');
        $attachmentsProperty->setAccessible(true);
        $attachments = $attachmentsProperty->getValue($sentMessage);

        $dataPartsData = [];

        if (is_array($attachments)) {
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

                $dataPartData = [
                    'path'     => $path,
                    'filename' => $filename,
                ];

                $dataPartsData[] = $dataPartData;
            }
        }

        $this->notificationProperties['details'] = Dumper::dump([
            'notifiable'   => $notificationSent->notifiable,
            'notification' => $notificationSent->notification,
        ]);

        $this->notificationProperties['channel'] = $notificationSent->channel;

        $this->notificationProperties['attachments'] = $dataPartsData;
        /** @phpstan-ignore-next-line */
        $this->notificationProperties['headers'] = $sentMessage->getHeaders()->toArray();
    }

    public function type(): string
    {
        return 'notification';
    }

    public function content(): array
    {
        return $this->notificationProperties;
    }
}
