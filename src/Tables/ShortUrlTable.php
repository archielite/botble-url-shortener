<?php

namespace ArchiElite\ShortenerUrl\Tables;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use ArchiElite\ShortenerUrl\Models\Analytics;
use ArchiElite\ShortenerUrl\Repositories\Interfaces\ShortUrlInterface;
use Botble\Table\Abstracts\TableAbstract;
use Collective\Html\HtmlFacade as Html;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class ShortUrlTable extends TableAbstract
{
    protected $hasActions = true;

    protected $hasFilter = true;

    public function __construct(DataTables $table, UrlGenerator $urlGenerator, ShortUrlInterface $shortUrlRepository)
    {
        $this->repository = $shortUrlRepository;
        $this->setOption('id', 'table-plugins-url_shortener');
        parent::__construct($table, $urlGenerator);

        if (! Auth::user()->hasAnyPermission(['url_shortener.edit', 'url_shortener.destroy'])) {
            $this->hasOperations = false;
            $this->hasActions = false;
        }
    }

    public function ajax(): JsonResponse
    {
        $data = $this->table
            ->eloquent($this->query())
            ->editColumn('long_url', function ($item) {
                if (! Auth::user()->hasPermission('url_shortener.edit')) {
                    return Str::limit($item->long_url, 25);
                }

                return Html::link(route('url_shortener.edit', $item->id), Str::limit($item->long_url, 25));
            })
            ->editColumn('checkbox', function ($item) {
                return $this->getCheckbox($item->id);
            })
            ->editColumn('user_id', function ($item) {
                return number_format(Analytics::getClicks($item->short_url));
            })
            ->editColumn('short_url', function ($item) {
                return Html::link(route('url_shortener.go', $item->short_url), $item->short_url);
            })
            ->editColumn('created_at', function ($item) {
                return BaseHelper::formatDate($item->created_at);
            })
            ->editColumn('status', function ($item) {
                return $item->status->toHtml();
            })
            ->addColumn('operations', function ($item) {
                $extra = Html::link(
                    route('url_shortener.analytics', $item->short_url),
                    __('Analytics'),
                    ['class' => 'btn btn-info']
                )->toHtml();

                return $this->getOperations('url_shortener.edit', 'url_shortener.destroy', $item, $extra);
            });

        return $this->toJson($data);
    }

    public function query(): Relation|Builder|QueryBuilder
    {
        $query = $this->repository
            ->select([
                'id',
                'long_url',
                'short_url',
                'created_at',
                'user_id',
                'status',
            ]);

        return $this->applyScopes($query);
    }

    public function columns(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'title' => trans('core/base::tables.id'),
                'width' => '20px',
            ],
            'long_url' => [
                'name' => 'long_url',
                'title' => __('URL'),
                'class' => 'text-start',
            ],
            'short_url' => [
                'name' => 'short_url',
                'title' => __('Short URL'),
                'class' => 'text-start',
            ],
            'user_id' => [
                'name' => 'user_id',
                'title' => __('Clicks'),
                'class' => 'text-center',
            ],
            'created_at' => [
                'name' => 'created_at',
                'title' => trans('core/base::tables.created_at'),
                'width' => '100px',
            ],
            'status' => [
                'name' => 'status',
                'title' => trans('core/base::tables.status'),
                'width' => '100px',
                'class' => 'text-center',
            ],
        ];
    }

    public function buttons(): array
    {
        return $this->addCreateButton(route('url_shortener.create'), 'url_shortener.create');
    }

    public function bulkActions(): array
    {
        return $this->addDeleteAction(route('url_shortener.deletes'), 'url_shortener.destroy', parent::bulkActions());
    }

    public function getBulkChanges(): array
    {
        return [
            'long_url' => [
                'title' => __('URL'),
                'type' => 'text',
                'validate' => 'required|max:255',
            ],
            'created_at' => [
                'title' => trans('core/base::tables.created_at'),
                'type' => 'date',
            ],
            'status' => [
                'title' => trans('core/base::tables.status'),
                'type' => 'select',
                'choices' => BaseStatusEnum::labels(),
                'validate' => 'required|in:' . implode(',', BaseStatusEnum::values()),
            ],
        ];
    }

    public function getOperationsHeading(): array
    {
        return [
            'operations' => [
                'title' => trans('core/base::tables.operations'),
                'width' => '300px',
                'class' => 'text-center',
                'orderable' => false,
                'searchable' => false,
                'exportable' => false,
                'printable' => false,
            ],
        ];
    }
}
