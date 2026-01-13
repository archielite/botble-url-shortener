<?php

namespace ArchiElite\UrlShortener\Traits;

use ArchiElite\UrlShortener\Services\QrCodeService;

trait HasQrCode
{
    public function getQrCodeUrl(array $options = []): string
    {
        $qrCodeService = app(QrCodeService::class);
        $fullUrl = route('url_shortener.go', $this->short_url);

        return $qrCodeService->generateQrCodeUrl($fullUrl, $options);
    }

    public function getQrCodeImage(array $options = []): ?string
    {
        $qrCodeService = app(QrCodeService::class);
        $fullUrl = route('url_shortener.go', $this->short_url);

        return $qrCodeService->generateQrCodeImage($fullUrl, $options);
    }

    public function getQrCodePageUrl(): string
    {
        return route('url_shortener.qr-code.show', $this->short_url);
    }

    public function getQrCodeDownloadUrl(array $options = []): string
    {
        return route('url_shortener.qr-code.download', array_merge(
            ['short_url' => $this->short_url],
            $options
        ));
    }

    public function clearQrCodeCache(): void
    {
        $qrCodeService = app(QrCodeService::class);
        $fullUrl = route('url_shortener.go', $this->short_url);

        $qrCodeService->clearCache($fullUrl);
    }

    public function qrCode(): string
    {
        return $this->getQrCodeUrl();
    }
}
