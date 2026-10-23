<?php

namespace App\Mail\Transport;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Mail\Transport\ResendTransport;
use Resend\Client;
use Resend\Transporters\HttpTransporter;
use Resend\ValueObjects\ApiKey;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;

class TimedResendTransport extends ResendTransport
{
    public function __construct(
        string $apiKey,
        string $baseUrl = 'api.resend.com',
        int $timeout = 8,
        int $connectTimeout = 3,
    ) {
        $resend = new Client(new HttpTransporter(
            new GuzzleClient([
                'timeout' => max(1, $timeout),
                'connect_timeout' => max(1, min($connectTimeout, $timeout)),
            ]),
            BaseUri::from($baseUrl),
            Headers::withAuthorization(ApiKey::from($apiKey)),
        ));

        parent::__construct($resend);
    }
}
