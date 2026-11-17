<?php
declare(strict_types=1);

return [
    'smtp_host'  => 'smtp.gmail.com',
    'smtp_port'  => 587,
    'smtp_user'  => 'reviewfilm_tokusatsu.noreply@gmail.com',
    'smtp_pass'  => '',
    'from_email' => 'noreply@tokusatsu.com',
    'from_name'  => 'Tokusatsu Universe Security Team',
    'log_dir'    => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'mail_logs',
];
