@extends('core/base::layouts.master')
@section('content')
    <div>
        <div class="card-header d-flex justify-content-end">
            <a href="{{ route('url_shortener.edit', $shortUrl)  }}"
               class="btn btn-success d-flex text-center align-items-center">{{ trans('plugins/url-shortener::url-shortener.edit', ['name' => $shortUrl->short_url]) }}</a>
        </div>
        <div class="card-body">
            <p>{{ trans('plugins/url-shortener::analytics.created_at', ['date' => $creationDate]) }}</p>
            <div class="row">
                <div class="mb-3 widget-item col-md-4">
                    <div class="h-100 bg-white-opacity position-relative">
                        <div class="d-flex p-2 pt-3 position-relative">
                            <div class="block-left d-flex mr-1">
                                <span class="align-self-center bg-white p-1">
                                    <i class="fa-solid fa-arrow-pointer fa-2x m-2"></i>
                                </span>
                            </div>
                            <div class="block-content mx-3">
                                <p class="mb-1">{{ trans('plugins/url-shortener::analytics.click.clicks') }}</p>
                                <h5>{{ $clicks }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 widget-item col-md-4">
                    <div class="h-100 bg-white-opacity position-relative">
                        <div class="d-flex p-2 pt-3 position-relative">
                            <div class="block-left d-flex mr-1">
                                <span class="align-self-center bg-white p-1">
                                    <i class="fa fa-hand-pointer fa-2x m-2"></i>
                                </span>
                            </div>
                            <div class="block-content mx-3">
                                <p class="mb-1">{{ trans('plugins/url-shortener::analytics.click.reals') }}</p>
                                <h5>{{ $realClicks }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-3 widget-item col-md-4">
                    <div class="h-100 bg-white-opacity position-relative">
                        <div class="d-flex p-2 pt-3 position-relative">
                            <div class="block-left d-flex mr-1">
                                <span class="align-self-center bg-white p-1">
                                    <i class="fas fa-clock fa-2x m-2"></i>
                                </span>
                            </div>
                            <div class="block-content mx-3">
                                <p class="mb-1">{{ trans('plugins/url-shortener::analytics.click.today') }}</p>
                                <h5>{{ $todayClicks }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="bg-white p-3">
                        <h5>{{ trans('plugins/url-shortener::analytics.country.real') }}</h5>
                        @if (count($countriesRealViews) > 0)
                            <div class="chart">
                                <canvas id="chart-pie-countries-real"></canvas>
                            </div>
                        @else
                            <p>{{ trans('plugins/url-shortener::analytics.country.na') }}</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-white p-3">
                        <h5>{{ trans('plugins/url-shortener::analytics.country.views') }}</h5>
                        @if (count($countriesViews) > 0)
                            <div class="chart">
                                <canvas id="chart-pie-countries"></canvas>
                            </div>
                        @else
                            <p>{{ trans('plugins/url-shortener::analytics.country.na') }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-between bg-white pt-3 mb-3" id="referrers-table">
                <h5 class="px-3">{{ trans('plugins/url-shortener::analytics.referer.referrers') }}</h5>
                <p class="px-3"> {{ trans('plugins/url-shortener::analytics.referer.list.results', [
                            'firstItem' => $referrers->firstItem(),
                            'lastItem' => $referrers->lastItem(),
                            'num' => $referrers->total()
                            ]) }}</p>
            </div>
            <div id="table-component" class="tab-pane tab-example-result fade active show bg-white mb-3"
                 role="tabpanel" aria-labelledby="table-component-tab">
                <div class="table-responsive">
                    @if ($referrers->count())
                        <table class="table align-items-center">
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">{{ trans('plugins/url-shortener::analytics.url.url') }}</th>
                                <th scope="col">{{ trans('plugins/url-shortener::analytics.view.reals') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($referrers as $referer)
                                <tr>
                                    <th scope="row">
                                        @if ($referer->referer == 'Direct / Unknown')
                                            <p>{{$referer->referer}}</p>
                                        @else
                                            <p>
                                                <a class="text-lowercase" href="{{$referer->referer}}">{{$referer->referer}}</a>
                                            </p>
                                        @endif
                                    </th>
                                    <td>
                                        <p>{{$referer->total}}</p>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="pt-3 px-3">{{ trans('plugins/url-shortener::analytics.referer.na') }}</p>
                    @endif
                </div>
            </div>
            {{ $referrers->fragment('referrers-table')->links() }}
        </div>
    </div>
@endsection
@push('footer')
    <script>
        'use strict'

        $(document).ready(function() {
            new Chart(document.getElementById('chart-pie-countries-real').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [
                        @foreach ($countriesRealViews as $country => $value)
                            '{{ $country }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: '# of Votes',
                        data: [
                            @foreach ($countriesRealViews as $country => $value)
                                {{ $value }},
                            @endforeach
                        ],
                        backgroundColor: [
                            @foreach ($countriesColor as $color)
                                'rgba({{ $color }}, 0.5)',
                            @endforeach
                        ],
                        borderColor: [
                            @foreach ($countriesColor as $color)
                                'rgba({{ $color }}, 1)',
                            @endforeach
                        ],
                        borderWidth: 1,
                    }],
                },
            });

            new Chart(document.getElementById('chart-pie-countries').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: [
                        @foreach ($countriesViews as $country => $value)
                            '{{ $country }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: '# of Votes',
                        data: [
                            @foreach ($countriesViews as $country => $value)
                                {{ $value }},
                            @endforeach
                        ],
                        backgroundColor: [
                            @foreach ($countriesColor as $color)
                                'rgba({{ $color }}, 0.5)',
                            @endforeach
                        ],
                        borderColor: [
                            @foreach ($countriesColor as $color)
                                'rgba({{ $color }}, 1)',
                            @endforeach
                        ],
                        borderWidth: 1,
                    }],
                },
            });
        });
    </script>
@endpush
