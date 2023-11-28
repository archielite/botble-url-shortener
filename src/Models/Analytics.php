<?php

namespace ArchiElite\UrlShortener\Models;

use Botble\Base\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Analytics extends BaseModel
{
    protected $table = 'short_url_analytics';

    protected $fillable = [
        'short_url',
        'click',
        'real_click',
        'country',
        'country_full',
        'referer',
        'ip_address',
    ];

    public static function store($data): void
    {
        $viewUrl = new Analytics();
        $viewUrl->fill($data);
        $viewUrl->save();
    }

    public static function realClick($shortUrl, $ipAddress): bool
    {
        $view = self::where('short_url', $shortUrl)
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->limit(1)
            ->orderBy('created_at', 'desc')
            ->get();

        return ! $view->isNotEmpty();
    }

    public static function getReferrers($url)
    {
        return Analytics::where('short_url', $url)
            ->where('real_click', 1)
            ->select(DB::raw('IFNULL(referer, \'Direct / Unknown\') AS referer'), DB::raw('sum(real_click) as total'))
            ->groupBy('real_click', 'referer')
            ->orderBy('total', 'DESC')
            ->paginate(20);
    }

    public static function getClicks($url): int
    {
        return Analytics::where('short_url', $url)
            ->count();
    }

    public static function getRealClicks($url): int
    {
        return Analytics::where('short_url', $url)
            ->where('real_click', 1)
            ->count();
    }

    public static function getTodayClicks($url): int
    {
        return Analytics::where('short_url', $url)
            ->where('created_at', '>=', now()->subDay())
            ->count();
    }

    public static function getCountriesViews($url): array
    {
        return Analytics::where('short_url', $url)
            ->select('country_full', DB::raw('count(*) as total'))
            ->groupBy('country_full')
            ->pluck('total', 'country_full')
            ->all();
    }

    public static function getCountriesRealViews($url): array
    {
        return Analytics::where('short_url', $url)
            ->where('real_click', 1)
            ->select('country_full', DB::raw('count(*) as total'))
            ->groupBy('country_full')
            ->pluck('total', 'country_full')
            ->all();
    }

    public static function getCountriesColor($countriesViews): array
    {
        $rgbColor = [];
        $countriesColor = [];
        $countriesNum = count($countriesViews);

        // Iterate same time as the number of the countries
        for ($i = 0; $i <= $countriesNum; $i++) {
            foreach (['r', 'g', 'b'] as $color) {
                $rgbColor[$color] = mt_rand(0, 255);
            }

            $countriesColor[] = $rgbColor['r'] . ', ' . $rgbColor['g'] . ', ' . $rgbColor['b'];
        }

        return $countriesColor;
    }

    public static function getCreationDate($url)
    {
        $creationDate = UrlShortener::where('short_url', $url)
            ->select('created_at')
            ->first('created_at');

        if (! $creationDate) {
            return null;
        }

        return $creationDate->created_at->diffForHumans();
    }
}
