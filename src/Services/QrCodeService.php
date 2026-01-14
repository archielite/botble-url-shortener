<?php

namespace ArchiElite\UrlShortener\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    protected const API_BASE_URL = 'https://api.qrserver.com/v1/create-qr-code/';
    protected const CACHE_PREFIX = 'qr_code_';
    protected const CACHE_TTL = 86400;

    public function generateQrCodeUrl(string $data, array $options = []): string
    {
        $params = array_merge($this->getDefaultOptions(), $options, [
            'data' => $data,
        ]);

        return self::API_BASE_URL . '?' . http_build_query($params);
    }

    public function generateQrCodeImage(string $data, array $options = []): ?string
    {
        $cacheKey = $this->getCacheKey($data, $options);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($data, $options) {
            try {
                $url = $this->generateQrCodeUrl($data, $options);
                $response = Http::timeout(10)->get($url);

                if ($response->successful()) {
                    return $response->body();
                }

                Log::error('QR Code API Error', [
                    'url' => $url,
                    'status' => $response->status(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('QR Code Generation Failed', [
                    'message' => $e->getMessage(),
                    'data' => $data,
                ]);

                return null;
            }
        });
    }

    protected function getDefaultOptions(): array
    {
        return [
            'size' => config('plugins.url-shortener.qr-code.size', '300x300'),
            'ecc' => config('plugins.url-shortener.qr-code.ecc', 'L'),
            'format' => config('plugins.url-shortener.qr-code.format', 'png'),
            'color' => config('plugins.url-shortener.qr-code.color', '0-0-0'),
            'bgcolor' => config('plugins.url-shortener.qr-code.bgcolor', '255-255-255'),
            'qzone' => config('plugins.url-shortener.qr-code.qzone', 1),
            'margin' => config('plugins.url-shortener.qr-code.margin', 1),
        ];
    }

    public function getAvailableSizes(): array
    {
        return [
            '100x100' => '100x100 px (Small)',
            '200x200' => '200x200 px (Medium)',
            '300x300' => '300x300 px (Large)',
            '400x400' => '400x400 px (Extra Large)',
            '500x500' => '500x500 px (XXL)',
            '800x800' => '800x800 px (Print Quality)',
        ];
    }

    public function getErrorCorrectionLevels(): array
    {
        return [
            'L' => 'Low (~7% correction)',
            'M' => 'Medium (~15% correction)',
            'Q' => 'Quality (~25% correction)',
            'H' => 'High (~30% correction)',
        ];
    }

    public function getAvailableFormats(): array
    {
        return [
            'png' => 'PNG',
            'gif' => 'GIF',
            'jpeg' => 'JPEG',
            'jpg' => 'JPG',
            'svg' => 'SVG (Vector)',
            'eps' => 'EPS (Vector)',
        ];
    }

    protected function getCacheKey(string $data, array $options): string
    {
        $key = md5($data . serialize($options));
        return self::CACHE_PREFIX . $key;
    }

    public function clearCache(string $data, array $options = []): void
    {
        $cacheKey = $this->getCacheKey($data, $options);
        Cache::forget($cacheKey);
    }

    public function validateOptions(array $options): bool
    {
        if (isset($options['size'])) {
            if (!preg_match('/^\d+x\d+$/', $options['size'])) {
                return false;
            }

            [$width, $height] = explode('x', $options['size']);
            if ($width < 10 || $width > 1000 || $height < 10 || $height > 1000 || $width !== $height) {
                return false;
            }
        }

        if (isset($options['ecc']) && !in_array($options['ecc'], ['L', 'M', 'Q', 'H'])) {
            return false;
        }

        if (isset($options['format']) && !in_array($options['format'], ['png', 'gif', 'jpeg', 'jpg', 'svg', 'eps'])) {
            return false;
        }

        foreach (['color', 'bgcolor'] as $colorField) {
            if (isset($options[$colorField])) {
                if (!$this->isValidColor($options[$colorField])) {
                    return false;
                }
            }
        }

        if (isset($options['margin'])) {
            if (!is_numeric($options['margin']) || $options['margin'] < 0 || $options['margin'] > 50) {
                return false;
            }
        }

        if (isset($options['qzone'])) {
            if (!is_numeric($options['qzone']) || $options['qzone'] < 0 || $options['qzone'] > 100) {
                return false;
            }
        }

        return true;
    }

    protected function isValidColor(string $color): bool
    {
        if (preg_match('/^(\d{1,3})-(\d{1,3})-(\d{1,3})$/', $color, $matches)) {
            return $matches[1] <= 255 && $matches[2] <= 255 && $matches[3] <= 255;
        }

        if (preg_match('/^[a-fA-F0-9]{3}$/', $color) || preg_match('/^[a-fA-F0-9]{6}$/', $color)) {
            return true;
        }

        return false;
    }

    public function getMimeType(string $format): string
    {
        return match ($format) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'jpeg', 'jpg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'eps' => 'application/postscript',
            default => 'image/png',
        };
    }
}
