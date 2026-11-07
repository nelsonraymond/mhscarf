<?php

namespace Tests\Unit;

use App\Mail\Transport\TimedResendTransport;
use App\Mail\ReviewReminderMail;
use App\Models\Order;
use Mockery;
use PHPUnit\Framework\TestCase;
use Resend\Client;
use Resend\Contracts\Transporter;
use Resend\ValueObjects\ApiKey;
use Resend\ValueObjects\Transporter\BaseUri;
use Resend\ValueObjects\Transporter\Headers;
use Resend\ValueObjects\Transporter\Payload;
use Symfony\Component\Mime\Email;

class TimedResendTransportTest extends TestCase
{
    public function test_review_reminder_adds_the_stable_provider_idempotency_header(): void
    {
        $order = new Order;
        $order->setAttribute('id', 42);
        $order->setAttribute('order_number', 'ORD-TEST-42');
        $mail = new ReviewReminderMail($order, collect(), 'review-reminder/order/42');
        $message = new Email;

        foreach ($mail->envelope()->using as $callback) {
            $callback($message);
        }

        $this->assertSame(
            'review-reminder/order/42',
            $message->getHeaders()->get('X-Resend-Idempotency-Key')->getBodyAsString(),
        );
    }

    public function test_resend_idempotency_key_is_sent_as_provider_request_header(): void
    {
        $transporter = Mockery::mock(Transporter::class);
        $transporter->shouldReceive('request')->once()->with(Mockery::type(Payload::class))
            ->andReturnUsing(function (Payload $payload): array {
                $request = $payload->toRequest(
                    BaseUri::from('api.resend.com'),
                    Headers::withAuthorization(ApiKey::from('test-api-key')),
                );

                $this->assertSame('review-reminder/order/42', $request->getHeaderLine('Idempotency-Key'));
                $this->assertStringContainsString('Review pending purchases', (string) $request->getBody());
                $this->assertStringNotContainsString('X-Resend-Idempotency-Key', (string) $request->getBody());

                return ['id' => 'resend-test-message-id'];
            });

        $transport = new TimedResendTransport('', resendClient: new Client($transporter));
        $email = (new Email())
            ->from('orders@example.test')
            ->to('customer@example.test')
            ->subject('Review pending purchases')
            ->text('Please review your order.');
        $email->getHeaders()->addTextHeader('X-Resend-Idempotency-Key', 'review-reminder/order/42');

        $sent = $transport->send($email);

        $this->assertSame('resend-test-message-id', $sent->getMessageId());
    }
}
