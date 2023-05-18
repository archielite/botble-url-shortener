<?php

namespace ArchiElite\ShortUrl\Http\Controllers;

use App\Http\Controllers\Controller;
use Botble\Base\Enums\BaseStatusEnum;
use ArchiElite\ShortUrl\Models\Analytics;
use ArchiElite\ShortUrl\Models\ShortUrl;
use Botble\Base\Facades\PageTitle;
use Exception;
use GeoIp2\Database\Reader;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function view($url, Request $request)
    {
        $result = ShortUrl::where('short_url', $url)->where('status', BaseStatusEnum::PUBLISHED)->first();

        if (! $result) {
            return redirect()->route('public.index');
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
        PageTitle::setTitle(trans('plugins/short-url::analytics.show.title', ['name' => $url]));

        $shortUrl = ShortUrl::where('short_url', $url)->firstOrFail();

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

        return view('plugins/short-url::analytics')->with($data);
    }
}
