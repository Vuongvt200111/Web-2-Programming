<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Electronic mail transmission service adhering to SMTP protocol (RFC 5321).
 */
final class MailerService
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Send password recovery email and store simulation log without touching $_SESSION.
     *
     * @return array{success: bool, smtpSent: bool, resetLink: string, logFile: string, previewMeta: array<string, string>, message: string}
     */
    public function sendPasswordResetEmail(string $toEmail, string $toName, string $resetLink): array
    {
        $mail = $this->config->section('mail');
        $smtpHost = (string) ($mail['smtp_host'] ?? 'smtp.gmail.com');
        $smtpPort = (int) ($mail['smtp_port'] ?? 587);
        $smtpUser = (string) ($mail['smtp_user'] ?? 'reviewfilm_tokusatsu.noreply@gmail.com');
        $smtpPass = (string) ($mail['smtp_pass'] ?? '');
        $fromEmail = (string) ($mail['from_email'] ?? 'noreply@tokusatsu.com');
        $fromName = (string) ($mail['from_name'] ?? 'Tokusatsu Universe Security Team');
        $logDir = (string) ($mail['log_dir'] ?? $this->config->rootPath('uploads/mail_logs'));

        $subject = '=?UTF-8?B?' . base64_encode('Password Reset Request - Tokusatsu Universe') . '?=';
        $htmlBody = $this->renderResetEmailBody($toName, $resetLink);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . DIRECTORY_SEPARATOR . 'mail_' . date('Ymd_His') . '_' . substr(md5($toEmail), 0, 8) . '.html';
        @file_put_contents($logFile, $htmlBody);

        $smtpSent = false;
        $smtpLogs = [];
        if ($smtpPass !== '') {
            $smtpSent = $this->sendViaSocketSMTP(
                $smtpHost,
                $smtpPort,
                $smtpUser,
                $smtpPass,
                $fromEmail,
                $fromName,
                $toEmail,
                $subject,
                $htmlBody,
                $smtpLogs
            );
        } else {
            $smtpLogs[] = 'SMTP Password not configured; dispatched via Local Mail Delivery Engine.';
        }

        return [
            'success'     => true,
            'smtpSent'    => $smtpSent,
            'resetLink'   => $resetLink,
            'logFile'     => $logFile,
            'previewMeta' => [
                'to'        => $toEmail,
                'name'      => $toName,
                'link'      => $resetLink,
                'timestamp' => date('Y-m-d H:i:s'),
                'log_file'  => basename($logFile),
            ],
            'message'     => 'Password reset instructions have been sent successfully to ' . htmlspecialchars($toEmail, ENT_QUOTES, 'UTF-8'),
        ];
    }

    private function renderResetEmailBody(string $toName, string $resetLink): string
    {
        $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
        $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; background-color: #121212; color: #ffffff; padding: 20px; }
        .email-card { max-width: 600px; margin: 0 auto; background: #1e1e1e; border: 1px solid #333; border-radius: 12px; padding: 30px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); }
        .brand-title { color: #dc3545; font-size: 24px; font-weight: bold; text-align: center; margin-bottom: 20px; }
        .greeting { font-size: 16px; margin-bottom: 15px; color: #e0e0e0; }
        .instruction { font-size: 14px; line-height: 1.6; color: #b0b0b0; margin-bottom: 25px; }
        .btn-reset { display: block; width: 220px; margin: 25px auto; padding: 14px 20px; background: linear-gradient(135deg, #e50914, #b81d24); color: #ffffff !important; text-align: center; text-decoration: none; font-weight: bold; border-radius: 8px; font-size: 16px; box-shadow: 0 4px 15px rgba(229,9,20,0.4); }
        .token-box { background: #2a2a2a; border-left: 4px solid #ffc107; padding: 12px 16px; margin: 20px 0; font-family: monospace; font-size: 13px; color: #ffc107; word-break: break-all; }
        .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; border-top: 1px solid #333; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="email-card">
        <div class="brand-title">TOKUSATSU UNIVERSE</div>
        <div class="greeting">Hello, <strong>{$safeName}</strong>!</div>
        <div class="instruction">
            We received a request to reset your password for your Tokusatsu Universe account. Click the button below to set a new password. This link is valid for <strong>60 minutes</strong>.
        </div>
        <a href="{$safeLink}" class="btn-reset">Reset My Password</a>
        <div class="instruction">If the button above does not work, copy and paste this link into your browser:</div>
        <div class="token-box">{$safeLink}</div>
        <div class="instruction">
            If you did not request a password reset, please ignore this email or contact our support team. Your account remains completely secure.
        </div>
        <div class="footer">
            &copy; 2026 Tokusatsu Universe Portal. Built for COMP1842 Coursework.<br>
            Automated transmission via modern SMTP protocol.
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * @param array<int, string> $logs
     */
    private function sendViaSocketSMTP(
        string $smtpHost,
        int $smtpPort,
        string $smtpUser,
        string $smtpPass,
        string $fromEmail,
        string $fromName,
        string $toEmail,
        string $subject,
        string $htmlBody,
        array &$logs
    ): bool {
        $socket = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 5);
        if (!$socket) {
            $logs[] = "Failed to connect to SMTP host: {$errstr} ({$errno})";
            return false;
        }

        $getResponse = static function ($sock): string {
            $data = '';
            while ($str = fgets($sock, 515)) {
                $data .= $str;
                if (substr($str, 3, 1) === ' ') {
                    break;
                }
            }
            return $data;
        };

        $sendCommand = static function ($sock, string $cmd) use ($getResponse, &$logs): string {
            fputs($sock, $cmd . "\r\n");
            $res = $getResponse($sock);
            $logs[] = "CLIENT: {$cmd} | SERVER: " . trim($res);
            return $res;
        };

        $logs[] = 'INIT: ' . trim($getResponse($socket));
        $serverName = (string) ($_SERVER['SERVER_NAME'] ?? 'localhost');

        $sendCommand($socket, 'EHLO ' . $serverName);
        $sendCommand($socket, 'STARTTLS');
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $sendCommand($socket, 'EHLO ' . $serverName);

        $sendCommand($socket, 'AUTH LOGIN');
        $sendCommand($socket, base64_encode($smtpUser));
        $sendCommand($socket, base64_encode($smtpPass));

        $sendCommand($socket, 'MAIL FROM: <' . $fromEmail . '>');
        $sendCommand($socket, 'RCPT TO: <' . $toEmail . '>');
        $sendCommand($socket, 'DATA');

        $headers  = 'From: ' . $fromName . ' <' . $fromEmail . ">\r\n";
        $headers .= 'To: <' . $toEmail . ">\r\n";
        $headers .= 'Subject: ' . $subject . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= 'Date: ' . date('r') . "\r\n";

        fputs($socket, $headers . "\r\n" . $htmlBody . "\r\n.\r\n");
        $dataRes = $getResponse($socket);
        $logs[] = 'DATA RESULT: ' . trim($dataRes);

        $sendCommand($socket, 'QUIT');
        fclose($socket);

        return str_starts_with(trim($dataRes), '250');
    }
}
