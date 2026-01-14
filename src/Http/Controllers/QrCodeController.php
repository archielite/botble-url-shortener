<?php

namespace ArchiElite\UrlShortener\Http\Controllers;

use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Services\QrCodeService;
use Botble\Base\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class QrCodeController extends BaseController
{
    public function __construct(protected QrCodeService $qrCodeService)
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/url-shortener::url-shortener.name'), route('url_shortener.index'));
    }

    public function show(string $shortUrl)
    {
        $urlShortener = UrlShortener::query()
            ->where('short_url', $shortUrl)
            ->firstOrFail();

        $this->pageTitle(trans('plugins/url-shortener::qr-code.title', ['name' => $shortUrl]));

        $this
            ->breadcrumb()
            ->add(trans('plugins/url-shortener::qr-code.title', ['name' => $shortUrl]));

        $fullUrl = route('url_shortener.go', $shortUrl);

        return view('plugins/url-shortener::qr-code', [
            'urlShortener' => $urlShortener,
            'fullUrl' => $fullUrl,
            'qrCodeUrl' => $this->qrCodeService->generateQrCodeUrl($fullUrl),
            'availableSizes' => $this->qrCodeService->getAvailableSizes(),
            'errorCorrectionLevels' => $this->qrCodeService->getErrorCorrectionLevels(),
            'availableFormats' => $this->qrCodeService->getAvailableFormats(),
        ]);
    }

    public function generate(Request $request, string $shortUrl)
    {
        UrlShortener::query()
            ->where('short_url', $shortUrl)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'size' => 'nullable|regex:/^\d+x\d+$/',
            'ecc' => 'nullable|in:L,M,Q,H',
            'format' => 'nullable|in:png,gif,jpeg,jpg,svg,eps',
            'color' => 'nullable|string|max:20',
            'bgcolor' => 'nullable|string|max:20',
            'margin' => 'nullable|integer|min:0|max:50',
            'qzone' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($validator->errors()->first());
        }

        $options = array_filter($request->only([
            'size',
            'ecc',
            'format',
            'color',
            'bgcolor',
            'margin',
            'qzone',
        ]));

        if (!$this->qrCodeService->validateOptions($options)) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/url-shortener::qr-code.invalid_options'));
        }

        $fullUrl = route('url_shortener.go', $shortUrl);
        $qrCodeUrl = $this->qrCodeService->generateQrCodeUrl($fullUrl, $options);

        return $this
            ->httpResponse()
            ->setData([
                'url' => $qrCodeUrl,
                'download_url' => route('url_shortener.qr-code.download', array_merge(['short_url' => $shortUrl], $options)),
            ]);
    }

    public function download(Request $request, string $shortUrl)
    {
        UrlShortener::query()
            ->where('short_url', $shortUrl)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'size' => 'nullable|regex:/^\d+x\d+$/',
            'ecc' => 'nullable|in:L,M,Q,H',
            'format' => 'nullable|in:png,gif,jpeg,jpg,svg,eps',
            'color' => 'nullable|string|max:20',
            'bgcolor' => 'nullable|string|max:20',
            'margin' => 'nullable|integer|min:0|max:50',
            'qzone' => 'nullable|integer|min:0|max:100',
        ]);

        if ($validator->fails()) {
            abort(400, $validator->errors()->first());
        }

        $options = array_filter($request->only([
            'size',
            'ecc',
            'format',
            'color',
            'bgcolor',
            'margin',
            'qzone',
        ]));

        if (!$this->qrCodeService->validateOptions($options)) {
            abort(400, trans('plugins/url-shortener::qr-code.invalid_options'));
        }

        $fullUrl = route('url_shortener.go', $shortUrl);
        $imageContent = $this->qrCodeService->generateQrCodeImage($fullUrl, $options);

        if (!$imageContent) {
            abort(500, trans('plugins/url-shortener::qr-code.generation_failed'));
        }

        $format = $options['format'] ?? 'png';
        $filename = "qr-code-{$shortUrl}.{$format}";
        $mimeType = $this->qrCodeService->getMimeType($format);

        return response($imageContent)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function image(Request $request, string $shortUrl)
    {
        UrlShortener::query()
            ->where('short_url', $shortUrl)
            ->firstOrFail();

        $options = array_filter($request->only([
            'size',
            'ecc',
            'format',
            'color',
            'bgcolor',
            'margin',
            'qzone',
        ]));

        if (!$this->qrCodeService->validateOptions($options)) {
            abort(400, trans('plugins/url-shortener::qr-code.invalid_options'));
        }

        $fullUrl = route('url_shortener.go', $shortUrl);
        $imageContent = $this->qrCodeService->generateQrCodeImage($fullUrl, $options);

        if (!$imageContent) {
            abort(500, trans('plugins/url-shortener::qr-code.generation_failed'));
        }

        $format = $options['format'] ?? 'png';
        $mimeType = $this->qrCodeService->getMimeType($format);

        return response($imageContent)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=86400');
    }

    public function clearCache(string $shortUrl)
    {
        UrlShortener::query()
            ->where('short_url', $shortUrl)
            ->firstOrFail();

        $fullUrl = route('url_shortener.go', $shortUrl);
        $this->qrCodeService->clearCache($fullUrl);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/url-shortener::qr-code.cache_cleared'));
    }
}
