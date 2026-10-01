<?php
/**
 * Mailer — wraps PHPMailer for all transactional emails
 * Falls back to graceful failure (logs error) if PHPMailer unavailable.
 */
class Mailer
{
    private bool $phpMailerAvailable;

    public function __construct()
    {
        $this->phpMailerAvailable = class_exists('PHPMailer\PHPMailer\PHPMailer');
    }

    // ── Public email methods ──────────────────────────────────

    public function sendWelcome(string $toEmail, string $toName, string $roleName): bool
    {
        $subject = 'Welcome to ' . SITE_NAME . '!';
        $body    = $this->loadTemplate('welcome.html', [
            '{{NAME}}'      => htmlspecialchars($toName),
            '{{ROLE}}'      => ucfirst(str_replace('_', ' ', $roleName)),
            '{{SITE_NAME}}' => SITE_NAME,
            '{{LOGIN_URL}}' => BASE_URL . '/login.php',
        ]);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendPasswordReset(string $toEmail, string $toName, string $resetUrl): bool
    {
        $subject = 'Reset your ' . SITE_NAME . ' password';
        $body    = $this->loadTemplate('reset-password.html', [
            '{{NAME}}'      => htmlspecialchars($toName),
            '{{RESET_URL}}' => $resetUrl,
            '{{SITE_NAME}}' => SITE_NAME,
            '{{EXPIRY}}'    => '1 hour',
        ]);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendOrderConfirmation(string $toEmail, string $toName,
                                          array $order, array $items): bool
    {
        $subject = 'Order Confirmed — #' . $order['order_number'];
        $body    = $this->loadTemplate('order-confirmation.html', [
            '{{NAME}}'         => htmlspecialchars($toName),
            '{{ORDER_NUMBER}}' => $order['order_number'],
            '{{TOTAL}}'        => formatPrice($order['total_amount']),
            '{{ORDER_URL}}'    => BASE_URL . '/customer/order-details.php?id=' . $order['id'],
            '{{SITE_NAME}}'    => SITE_NAME,
        ]);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendPaymentConfirmation(string $toEmail, string $toName,
                                            array $order, array $payment): bool
    {
        $subject = 'Payment Received — Order #' . $order['order_number'];
        $body    = $this->loadTemplate('payment-confirmation.html', [
            '{{NAME}}'         => htmlspecialchars($toName),
            '{{ORDER_NUMBER}}' => $order['order_number'],
            '{{AMOUNT}}'       => formatPrice($payment['amount']),
            '{{METHOD}}'       => str_replace('_', ' ', $payment['payment_method']),
            '{{REF}}'          => $payment['transaction_reference'],
            '{{ORDER_URL}}'    => BASE_URL . '/customer/order-details.php?id=' . $order['id'],
            '{{SITE_NAME}}'    => SITE_NAME,
        ]);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendQuotationNotification(string $toEmail, string $toName,
                                               string $subject, string $messageBody): bool
    {
        $body = $this->loadTemplate('quotation-notification.html', [
            '{{NAME}}'      => htmlspecialchars($toName),
            '{{MESSAGE}}'   => nl2br(htmlspecialchars($messageBody)),
            '{{SITE_NAME}}' => SITE_NAME,
            '{{PORTAL_URL}}'=> BASE_URL,
        ]);
        return $this->send($toEmail, $toName, $subject, $body);
    }

    public function sendShipmentUpdate(string $toEmail, string $toName,
                                       string $orderNumber, string $status,
                                       string $trackingNumber = ''): bool
    {
        $subject = 'Shipment Update — Order #' . $orderNumber;
        $body    = $this->loadTemplate('welcome.html', []); // fallback inline
        $html    = "
        <div style='font-family:IBM Plex Sans,sans-serif;max-width:600px;margin:auto'>
          <h2 style='color:#0a1630'>Shipment Update</h2>
          <p>Hi {$toName},</p>
          <p>Your order <strong>#{$orderNumber}</strong> shipment status has been updated to: <strong>{$status}</strong></p>"
          . ($trackingNumber ? "<p>Tracking number: <strong>{$trackingNumber}</strong></p>" : '')
          . "<p><a href='" . BASE_URL . "/customer/orders.php' style='color:#203a75'>View Order</a></p>
          <p>— " . SITE_NAME . " Team</p>
        </div>";
        return $this->send($toEmail, $toName, $subject, $html);
    }

    // ── Core send method ─────────────────────────────────────

    private function send(string $toEmail, string $toName,
                          string $subject, string $htmlBody): bool
    {
        if (!$this->phpMailerAvailable) {
            // Log and return true so the rest of the flow isn't blocked in dev
            error_log("Mailer: PHPMailer not available. Would send '$subject' to $toEmail");
            return true;
        }

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = MAIL_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = MAIL_USERNAME;
            $mail->Password   = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION === 'ssl'
                ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = MAIL_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($toEmail, $toName);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = strip_tags($htmlBody);

            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    // ── Template loader ──────────────────────────────────────

    private function loadTemplate(string $filename, array $replacements): string
    {
        $path = ROOT_PATH . '/includes/emails/' . $filename;
        if (!file_exists($path)) {
            // Fallback: plain HTML if template missing
            return '<p>' . implode('<br>', array_values($replacements)) . '</p>';
        }
        $html = file_get_contents($path);
        foreach ($replacements as $placeholder => $value) {
            $html = str_replace($placeholder, $value, $html);
        }
        return $html;
    }
}
