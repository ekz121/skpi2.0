<?php

namespace App\Services;

class MailConfiguration
{
    /**
     * @return list<string>
     */
    public function missing(): array
    {
        if (app()->environment('testing')) {
            return [];
        }

        $mailer = (string) config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            return ['MAIL_MAILER harus menggunakan smtp pada server produksi'];
        }

        if ($mailer !== 'smtp') {
            return [];
        }

        $fields = [
            'MAIL_HOST' => config('mail.mailers.smtp.host'),
            'MAIL_USERNAME' => config('mail.mailers.smtp.username'),
            'MAIL_PASSWORD' => config('mail.mailers.smtp.password'),
            'MAIL_FROM_ADDRESS' => config('mail.from.address'),
        ];

        $missing = [];
        foreach ($fields as $label => $value) {
            $normalized = strtolower(trim((string) $value));
            if ($normalized === '' || str_contains($normalized, 'ganti_') || str_contains($normalized, 'example.')) {
                $missing[] = $label;
            }
        }

        return $missing;
    }

    public function isReady(): bool
    {
        return $this->missing() === [];
    }
}
