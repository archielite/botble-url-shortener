<?php

namespace ArchiElite\UrlShortener\Tables\Actions;

use Botble\Table\Actions\Action;

class AnalyticAction extends Action
{
    public static function make(string $name = 'analytics'): static
    {
        return parent::make($name)
            ->color('info')
            ->icon('ti ti-brand-google-analytics')
            ->label(trans('plugins/url-shortener::analytics.analytics'))
            ->permission('url_shortener.analytics')
            ->url(function (Action $action) {
                $item = $action->getItem();

                if (! $item) {
                    return null;
                }

                return route('url_shortener.analytics', $item->short_url);
            });
    }
}
