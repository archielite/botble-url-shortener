@extends('core/base::layouts.master')
@section('content')
    <div class="row">
        <div class="col-md-8">
            <x-core::card>
                <x-core::card.header>
                    <h4 class="card-title"><i class="fas fa-qrcode"></i>
                        {{ trans('plugins/url-shortener::qr-code.qr_code_generator') }}</h4>
                </x-core::card.header>

                <x-core::card.body>
                    <x-core::form.text-input id="short-url" name="short_url"
                        label="{{ trans('plugins/url-shortener::qr-code.short_url') }}" value="{{ $fullUrl }}" readonly>
                        <x-slot name="append">
                            <x-core::button color="secondary" id="copy-btn">
                                <i class="fas fa-copy"></i> {{ trans('plugins/url-shortener::qr-code.copy') }}
                            </x-core::button>
                        </x-slot>
                    </x-core::form.text-input>

                    <div class="row">
                        <div class="col-md-6">
                            <x-core::form.select id="qr-size" name="qr_size"
                                label="{{ trans('plugins/url-shortener::qr-code.size') }}">
                                @foreach ($availableSizes as $value => $label)
                                    <option value="{{ $value }}" @selected($value === '300x300')>{{ $label }}
                                    </option>
                                @endforeach
                            </x-core::form.select>
                        </div>
                        <div class="col-md-6">
                            <x-core::form.select id="qr-ecc" name="qr_ecc"
                                label="{{ trans('plugins/url-shortener::qr-code.error_correction') }}">
                                @foreach ($errorCorrectionLevels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-core::form.select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <x-core::form.select id="qr-format" name="qr_format"
                                label="{{ trans('plugins/url-shortener::qr-code.format') }}">
                                @foreach ($availableFormats as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-core::form.select>
                        </div>
                        <div class="col-md-6">
                            <x-core::form.text-input id="qr-margin" name="qr_margin" type="number" value="1"
                                min="0" max="50"
                                label="{{ trans('plugins/url-shortener::qr-code.margin') }}" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <x-core::form.color-picker id="qr-color" name="qr_color"
                                label="{{ trans('plugins/url-shortener::qr-code.foreground_color') }}" value="0-0-0"
                                help="{{ trans('plugins/url-shortener::qr-code.color_format_help') }}" />
                        </div>
                        <div class="col-md-6">
                            <x-core::form.color-picker id="qr-bgcolor" name="qr_bgcolor"
                                label="{{ trans('plugins/url-shortener::qr-code.background_color') }}" value="255-255-255"
                                help="{{ trans('plugins/url-shortener::qr-code.color_format_help') }}" />
                        </div>
                    </div>

                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <x-core::button color="primary" id="generate-qr-btn">
                            <i class="fas fa-sync"></i> {{ trans('plugins/url-shortener::qr-code.generate') }}
                        </x-core::button>
                        <x-core::button color="success" id="download-qr-btn">
                            <i class="fas fa-download"></i> {{ trans('plugins/url-shortener::qr-code.download') }}
                        </x-core::button>
                        <x-core::button color="warning" id="clear-cache-btn">
                            <i class="fas fa-trash"></i> {{ trans('plugins/url-shortener::qr-code.clear_cache') }}
                        </x-core::button>
                    </div>
                </x-core::card.body>
            </x-core::card>
        </div>

        <div class="col-md-4">
            <x-core::card class="mb-3">
                <x-core::card.header>
                    <h4 class="card-title">{{ trans('plugins/url-shortener::qr-code.preview') }}</h4>
                </x-core::card.header>
                <x-core::card.body class="text-center">
                    <img src="{{ $qrCodeUrl }}" id="qr-image" class="img-fluid border p-2" alt="QR Code">
                    <div class="mt-2"><small
                            class="text-muted">{{ trans('plugins/url-shortener::qr-code.scan_info') }}</small></div>
                </x-core::card.body>
            </x-core::card>

            <x-core::card>
                <x-core::card.header>
                    <h4 class="card-title">{{ trans('plugins/url-shortener::qr-code.url_info') }}</h4>
                </x-core::card.header>
                <x-core::card.body>
                    <dl class="row mb-0">
                        <dt class="col-sm-5">{{ trans('plugins/url-shortener::url-shortener.alias') }}:</dt>
                        <dd class="col-sm-7">{{ $urlShortener->short_url }}</dd>

                        <dt class="col-sm-5">{{ trans('plugins/url-shortener::url-shortener.target_url') }}:</dt>
                        <dd class="col-sm-7">
                            <a href="{{ $urlShortener->long_url }}" target="_blank"
                                class="text-truncate d-block">{{ Str::limit($urlShortener->long_url, 30) }}</a>
                        </dd>

                        <dt class="col-sm-5">{{ trans('core/base::tables.status') }}:</dt>
                        <dd class="col-sm-7">{!! $urlShortener->status->toHtml() !!}</dd>

                        @if ($urlShortener->expired_at)
                            <dt class="col-sm-5">{{ trans('plugins/url-shortener::url-shortener.expired_at') }}:</dt>
                            <dd class="col-sm-7">{{ $urlShortener->expired_at->format('Y-m-d H:i') }}</dd>
                        @endif
                    </dl>
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>
@endsection

@push('footer')
    <script>
        'use strict';

        (function($) {
            const generateUrl = '{{ route('url_shortener.qr-code.generate', $urlShortener->short_url) }}';
            const downloadBaseUrl = '{{ route('url_shortener.qr-code.download', $urlShortener->short_url) }}';
            const clearCacheUrl = '{{ route('url_shortener.qr-code.clear-cache', $urlShortener->short_url) }}';

            function rgbStringToFormat(rgbString) {
                const match = rgbString.match(/rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*[\d.]+)?\)/);
                if (match) {
                    return `${match[1]}-${match[2]}-${match[3]}`;
                }
                return rgbString;
            }

            function hexToRgb(hex) {
                hex = hex.replace('#', '');
                const r = parseInt(hex.substr(0, 2), 16);
                const g = parseInt(hex.substr(2, 2), 16);
                const b = parseInt(hex.substr(4, 2), 16);
                return `${r}-${g}-${b}`;
            }

            function rgbToHex(rgb) {
                const parts = rgb.split('-').map(x => parseInt(x.trim()));
                if (parts.length !== 3 || parts.some(isNaN)) {
                    return '#000000';
                }
                const [r, g, b] = parts;
                return '#' + [r, g, b].map(x => {
                    const hex = Math.max(0, Math.min(255, x)).toString(16);
                    return hex.padStart(2, '0');
                }).join('');
            }

            function normalizeColorForAPI(colorValue) {
                if (!colorValue) {
                    return null;
                }

                if (/^\d{1,3}-\d{1,3}-\d{1,3}$/.test(colorValue)) {
                    return colorValue;
                }

                if (colorValue.startsWith('rgb')) {
                    return rgbStringToFormat(colorValue);
                }

                if (colorValue.startsWith('#')) {
                    return hexToRgb(colorValue);
                }

                if (/^[a-fA-F0-9]{6}$/.test(colorValue) || /^[a-fA-F0-9]{3}$/.test(colorValue)) {
                    return hexToRgb('#' + colorValue);
                }

                return colorValue;
            }

            function getColorValue(inputId) {
                const value = $(`#${inputId}`).val();
                const defaultColor = inputId === 'qr-color' ? '0-0-0' : '255-255-255';

                if (!value) {
                    return defaultColor;
                }

                return normalizeColorForAPI(value) || defaultColor;
            }

            function initColorPickers() {
                $('#qr-color').val('0-0-0');
                $('#qr-bgcolor').val('255-255-255');

                $('#qr-color, #qr-bgcolor').on('change input', function() {
                    generateQrCode();
                });
            }

            function showCopiedFeedback($btn) {
                const original = $btn.html();
                $btn.html('<i class="fas fa-check"></i> Copied')
                    .removeClass('btn-secondary')
                    .addClass('btn-success');
                setTimeout(() => {
                    $btn.html(original)
                        .removeClass('btn-success')
                        .addClass('btn-secondary');
                }, 2000);
            }

            function fallbackCopy(text, $btn) {
                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(text).select();
                try {
                    document.execCommand('copy');
                    showCopiedFeedback($btn);
                } catch (err) {
                    alert('Copy failed. Please copy manually: ' + text);
                }
                $temp.remove();
            }

            function getQrOptions() {
                const color = getColorValue('qr-color');
                const bgcolor = getColorValue('qr-bgcolor');

                return {
                    size: $('#qr-size').val() || '300x300',
                    ecc: $('#qr-ecc').val() || 'M',
                    format: $('#qr-format').val() || 'png',
                    color: color,
                    bgcolor: bgcolor,
                    margin: $('#qr-margin').val() || '1',
                    qzone: 1
                };
            }

            function generateQrCode() {
                const $btn = $('#generate-qr-btn');
                const originalText = $btn.html();

                $btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Generating...');

                const options = getQrOptions();

                $.ajax({
                    url: generateUrl,
                    method: 'POST',
                    data: options,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (!res.error && res.data && res.data.url) {
                            $('#qr-image').attr('src', res.data.url + '&t=' + Date.now());
                            if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                                Botble.showSuccess(res.message || 'QR code generated successfully!');
                            }
                        } else {
                            const errorMsg = res.message || 'Failed to generate QR code';
                            if (typeof Botble !== 'undefined' && Botble.showError) {
                                Botble.showError(errorMsg);
                            } else {
                                alert(errorMsg);
                            }
                        }
                    },
                    error: function(xhr) {
                        const errorMsg = xhr.responseJSON?.message ||
                            'An error occurred while generating QR code';
                        if (typeof Botble !== 'undefined' && Botble.showError) {
                            Botble.showError(errorMsg);
                        } else {
                            alert(errorMsg);
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            }

            $('#copy-btn').on('click', function() {
                const $btn = $(this);
                const url = $('#short-url').val();

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(url).then(() => {
                        showCopiedFeedback($btn);
                    }).catch(() => {
                        fallbackCopy(url, $btn);
                    });
                } else {
                    fallbackCopy(url, $btn);
                }
            });

            $('#download-qr-btn').on('click', function() {
                const options = getQrOptions();
                const params = new URLSearchParams(options);
                window.location.href = `${downloadBaseUrl}?${params.toString()}`;

                if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                    Botble.showSuccess('Download started');
                }
            });

            $('#clear-cache-btn').on('click', function() {
                if (!confirm('Are you sure you want to clear the QR code cache?')) {
                    return;
                }

                const $btn = $(this);
                const originalText = $btn.html();

                $btn.prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Clearing...');

                $.ajax({
                    url: clearCacheUrl,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        if (!res.error) {
                            if (typeof Botble !== 'undefined' && Botble.showSuccess) {
                                Botble.showSuccess(res.message || 'Cache cleared successfully');
                            }
                            generateQrCode();
                        } else {
                            if (typeof Botble !== 'undefined' && Botble.showError) {
                                Botble.showError(res.message || 'Failed to clear cache');
                            }
                        }
                    },
                    error: function() {
                        if (typeof Botble !== 'undefined' && Botble.showError) {
                            Botble.showError('An error occurred while clearing cache');
                        }
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });

            $('#qr-size, #qr-ecc, #qr-format, #qr-margin').on('change', function() {
                generateQrCode();
            });

            $(document).ready(function() {
                initColorPickers();

                setTimeout(function() {
                    $('#generate-qr-btn').trigger('click');
                }, 500);
            });

        })(jQuery);
    </script>
@endpush
