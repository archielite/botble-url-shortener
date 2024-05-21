<?php

namespace ArchiElite\UrlShortener\Http\Controllers;

use ArchiElite\UrlShortener\Models\Analytics;
use ArchiElite\UrlShortener\Models\UrlShortener;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Exception;
use GeoIp2\Database\Reader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AnalyticsController extends BaseController
{
    public function __construct()
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/url-shortener::url-shortener.name'), route('url_shortener.index'));
    }

    public function view($url, Request $request)
    {
        $result = UrlShortener::query()
            ->wherePublished()
            ->where('short_url', $url)
            ->first();

        if (! $result) {
            return redirect()->to(BaseHelper::getHomepageUrl());
        }

        $externalUrl = $result['long_url'];

        $ip = $request->ip();
        $referer = $request->server('HTTP_REFERER') ?? null;
        $hashed = 0;
        $countries = $this->getCountries($ip);

        if (Analytics::realClick($url, $ip)) {
            $click = 0;
            $realClick = 1;
        } else {
            $click = 1;
            $realClick = 0;
        }

        $data = [
            'short_url' => $url,
            'click' => $click,
            'real_click' => $realClick,
            'country' => $countries['countryCode'],
            'country_full' => $countries['countryName'],
            'referer' => $referer ?? null,
            'ip_address' => $ip,
            'ip_hashed' => $hashed,
        ];

        Analytics::store($data);

        return redirect()->away($externalUrl);
    }

    public function getCountries($ip)
    {
        // We try to get the IP country using (or not) the anonymized IP
        // If it fails, because GeoLite2 doesn't know the IP country, we
        // will set it to Unknown
        try {
            if (
                ! class_exists('GeoIp2\Database\Reader') &&
                File::exists($filePath = __DIR__ . '/../../../vendor/autoload.php')
            ) {
                require $filePath;

            }
            $reader = new Reader(__DIR__ . '/../../../database/GeoLite2-Country.mmdb');
            $record = $reader->country($ip);
            $countryCode = $record->country->isoCode;
            $countryName = $record->country->name;

            return compact('countryCode', 'countryName');
        } catch (Exception) {
            $countryCode = 'N/A';
            $countryName = 'Unknown';

            return compact('countryCode', 'countryName');
        }
    }

    public function show($url)
    {
        $this->pageTitle(trans('plugins/url-shortener::analytics.show.title', ['name' => $url]));

        $shortUrl = UrlShortener::query()
            ->wherePublished()
            ->where('short_url', $url)
            ->firstOrFail();

        $countriesViews = Analytics::getCountriesViews($url);

        $data = [
            'url' => $url,
            'shortUrl' => $shortUrl,
            'clicks' => Analytics::getClicks($url),
            'realClicks' => Analytics::getRealClicks($url),
            'todayClicks' => Analytics::getTodayClicks($url),
            'countriesViews' => $countriesViews,
            'countriesRealViews' => Analytics::getCountriesRealViews($url),
            'countriesColor' => Analytics::getCountriesColor($countriesViews),
            'referrers' => Analytics::getReferrers($url),
            'creationDate' => Analytics::getCreationDate($url),
        ];

        Assets::addScriptsDirectly([
            'vendor/core/plugins/url-shortener/js/chart.js/Chart.bundle.min.js',
            'vendor/core/plugins/url-shortener/js/chart.js/Chart.extension.js',
        ]);

        return view('plugins/url-shortener::analytics')->with($data);
    }
}
