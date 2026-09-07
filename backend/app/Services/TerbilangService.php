<?php

namespace App\Services;

class TerbilangService
{
    private array $ones = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima',
        'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh',
        'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas',
        'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas',
    ];

    /**
     * Konversi angka (rupiah) ke teks terbilang bahasa Indonesia.
     * Format output: (Delapan Ratus Ribu Rupiah)
     */
    public function convert(float|int $amount): string
    {
        $amount = (int) round($amount);

        if ($amount === 0) {
            return '(Nol Rupiah)';
        }

        return '(' . trim($this->spell($amount)) . ' Rupiah)';
    }

    private function spell(int $n): string
    {
        if ($n < 0) {
            return 'Minus ' . $this->spell(abs($n));
        }

        if ($n < 20) {
            return $this->ones[$n];
        }

        if ($n < 100) {
            $tens = ['', '', 'Dua Puluh', 'Tiga Puluh', 'Empat Puluh', 'Lima Puluh',
                'Enam Puluh', 'Tujuh Puluh', 'Delapan Puluh', 'Sembilan Puluh'];
            return $tens[(int)($n / 10)] . ($n % 10 !== 0 ? ' ' . $this->ones[$n % 10] : '');
        }

        if ($n < 200) {
            return 'Seratus' . ($n - 100 !== 0 ? ' ' . $this->spell($n - 100) : '');
        }

        if ($n < 1000) {
            return $this->ones[(int)($n / 100)] . ' Ratus' . ($n % 100 !== 0 ? ' ' . $this->spell($n % 100) : '');
        }

        if ($n < 2000) {
            return 'Seribu' . ($n - 1000 !== 0 ? ' ' . $this->spell($n - 1000) : '');
        }

        if ($n < 1_000_000) {
            return $this->spell((int)($n / 1000)) . ' Ribu' . ($n % 1000 !== 0 ? ' ' . $this->spell($n % 1000) : '');
        }

        if ($n < 1_000_000_000) {
            return $this->spell((int)($n / 1_000_000)) . ' Juta' . ($n % 1_000_000 !== 0 ? ' ' . $this->spell($n % 1_000_000) : '');
        }

        if ($n < 1_000_000_000_000) {
            return $this->spell((int)($n / 1_000_000_000)) . ' Miliar' . ($n % 1_000_000_000 !== 0 ? ' ' . $this->spell($n % 1_000_000_000) : '');
        }

        return $this->spell((int)($n / 1_000_000_000_000)) . ' Triliun' . ($n % 1_000_000_000_000 !== 0 ? ' ' . $this->spell($n % 1_000_000_000_000) : '');
    }
}
