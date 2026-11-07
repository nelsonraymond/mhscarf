<?php

namespace App\Mail\Transport;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Mail\Transport\ResendTransport;
use Resend\Client;
use Resend\Contracts\Client as ResendClient;
use Resend\Transporters\HttpTransporter;
use Resend\ValueObjects\ApiKey;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;
use Exception;

class TimedResendTransport extends ResendTransport
{
    public function __construct(
        string $apiKey,
        string $baseUrl = 'api.resend.com',
        int $timeout = 8,
        int $connectTimeout = 3,
        ?ResendClient $resendClient = null,
    ) {
        $resend = $resendClient ?? new Client(new HttpTransporter(
            new GuzzleClient([
                'timeout' => max(1, $timeout),
                'connect_timeout' => max(1, min($connectTimeout, $timeout)),
            ]),
            BaseUri::from($baseUrl),
            Headers::withAuthorization(ApiKey::from($apiKey)),
        ));

        parent::__construct($resend);
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $envelope = $message->getEnvelope();
        $headers = [];
        $idempotencyKey = null;
        $headersToBypass = [
            'from', 'to', 'cc', 'bcc', 'reply-to', 'sender', 'subject', 'content-type',
            'date', 'message-id', 'mime-version', 'content-transfer-encoding',
        ];

        foreach ($email->getHeaders()->all() as $name => $header) {
            if (strtolower($name) === 'x-resend-idempotency-key') {
                $idempotencyKey = $header->getBodyAsString();
                continue;
            }

            if (in_array(strtolower($name), $headersToBypass, true)) {
                continue;
            }

            $headers[$header->getName()] = $header->getBodyAsString();
        }

        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachmentHeaders = $attachment->getPreparedHeaders();
            $contentType = $attachmentHeaders->get('Content-Type')->getBody();
            $disposition = $attachmentHeaders->getHeaderBody('Content-Disposition');
            $filename = $attachmentHeaders->getHeaderParameter('Content-Disposition', 'filename');
            $content = $contentType === 'text/calendar'
                ? $attachment->getBody()
                : str_replace("\r\n", '', $attachment->bodyToString());

            $item = ['content_type' => $contentType, 'content' => $content, 'filename' => $filename];
            if ($disposition === 'inline') {
                $item['content_id'] = $attachment->hasContentId() ? $attachment->getContentId() : $filename;
            }
            $attachments[] = $item;
        }

        $payload = [
            'from' => $envelope->getSender()->toString(),
            'to' => $this->stringifyAddresses($this->getRecipients($email, $envelope)),
            'cc' => $this->stringifyAddresses($email->getCc()),
            'bcc' => $this->stringifyAddresses($email->getBcc()),
            'reply_to' => $this->stringifyAddresses($email->getReplyTo()),
            'headers' => $headers,
            'subject' => $email->getSubject(),
            'html' => $email->getHtmlBody(),
            'text' => $email->getTextBody(),
            'attachments' => $attachments,
        ];

        try {
            $options = $idempotencyKey === null ? [] : ['idempotency_key' => $idempotencyKey];
            $result = $this->resend->emails->send($payload, $options);

            if (isset($result['statusCode']) && $result['statusCode'] != Response::HTTP_OK) {
                throw new Exception($result['message']);
            }
        } catch (Exception $exception) {
            throw new TransportException(
                sprintf('Request to Resend API failed. Reason: %s.', $exception->getMessage()),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception,
            );
        }

        $messageId = $result->id;
        $message->setMessageId($messageId);

        if ($message->getOriginalMessage() instanceof \Symfony\Component\Mime\Message) {
            $message->getOriginalMessage()->getHeaders()->addHeader('X-Resend-Email-ID', $messageId);
        }
    }
}
