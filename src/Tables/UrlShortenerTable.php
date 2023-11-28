<?php

namespace ArchiElite\UrlShortener\Tables;

use ArchiElite\UrlShortener\Models\Analytics;
use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Tables\Actions\AnalyticAction;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\Actions\DeleteAction;
use Botble\Table\Actions\EditAction;
use Botble\Table\BulkActions\DeleteBulkAction;
use Botble\Table\BulkChanges\NameBulkChange;
use Botble\Table\Columns\CreatedAtColumn;
use Botble\Table\Columns\FormattedColumn;
use Botble\Table\Columns\IdColumn;
use Botble\Table\Columns\LinkableColumn;
use Botble\Table\Columns\StatusColumn;
use Botble\Table\HeaderActions\CreateHeaderAction;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class UrlShortenerTable extends TableAbstract
{
    public function setup(): void
    {
        $this
            ->model(UrlShortener::class)
            ->addHeaderAction(CreateHeaderAction::make()->url(route('url_shortener.create')))
            ->addColumns([
                IdColumn::make(),
                LinkableColumn::make('long_url')
                    ->urlUsing(function (LinkableColumn $column) {
                        $item = $column->getItem();

                        if (! $item) {
                            return null;
                        }

                        return route('url_shortener.edit', $item->getKey());
                    })
                    ->label(trans('plugins/url-shortener::url-shortener.url')),
                LinkableColumn::make('short_url')
                    ->urlUsing(function (LinkableColumn $column) {
                        $item = $column->getItem();

                        if (! $item) {
                            return null;
                        }

                        return route('url_shortener.go', $item->short_url);
                    })
                    ->label(trans('plugins/url-shortener::url-shortener.name'))
                    ->copyable()
                    ->copyableState(function (LinkableColumn $column) {
                        $item = $column->getItem();

                        if (! $item) {
                            return null;
                        }

                        return route('url_shortener.go', $item->short_url);
                    })
                ,
                FormattedColumn::make('clicks')
                    ->getValueUsing(function (FormattedColumn $column): int {
                        $item = $column->getItem();

                        if (! $item) {
                            return 0;
                        }

                        return Analytics::getClicks($item->short_url);
                    })
                ,
                CreatedAtColumn::make(),
                StatusColumn::make(),
            ])
            ->addActions([
                AnalyticAction::make(),
                EditAction::make()->route('url_shortener.edit'),
                DeleteAction::make()->route('url_shortener.destroy'),
            ])
            ->addBulkAction(DeleteBulkAction::make())
            ->addBulkChanges([
                NameBulkChange::make()
                    ->name('long_url')
                    ->title(trans('plugins/url-shortener::url-shortener.url')),
                NameBulkChange::make()
                    ->name('short_url')
                    ->title(trans('plugins/url-shortener::url-shortener.name')),
            ])
            ->queryUsing(function (EloquentBuilder $query) {
                return $query
                    ->select([
                        'id',
                        'long_url',
                        'short_url',
                        'created_at',
                        'status',
                    ]);
            });
    }
}
