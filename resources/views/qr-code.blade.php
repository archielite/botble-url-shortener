@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-qrcode"></i>
                        {{ trans('plugins/url-shortener::qr-code.qr_code_generator') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/url-shortener::qr-code.short_url') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $fullUrl }}" readonly>
                                    <button type="button" class="btn btn-secondary" onclick="copyToClipboard('{{ $fullUrl }}')">
                                        <i class="fas fa-copy"></i> {{ trans('plugins/url-shortener::qr-code.copy') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/url-shortener::qr-code.size') }}</label>
                                <select class="form-control" id="qr-size">
                                    @foreach($availableSizes as $value => $label)
                                        <option value="{{ $value }}" @selected($value === '300x300')>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/url-shortener::qr-code.error_correction') }}</label>
                                <select class="form-control" id="qr-ecc">
                                    @foreach($errorCorrectionLevels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/url-shortener::qr-code.format') }}</label>
                                <select class="form-control" id="qr-format">
                                    @foreach($availableFormats as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/url-shortener::qr-code.margin') }}</label>
                                <input type="number" class="form-control" id="qr-margin" value="1" min="0" max="50">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/url-shortener::qr-code.foreground_color') }}</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="qr-color-picker" value="#000000">
                                    <input type="text" class="form-control" id="qr-color" value="0-0-0" placeholder="0-0-0">
                                </div>
                                <small class="form-text text-muted">{{ trans('plugins/url-shortener::qr-code.color_format_help') }}</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label">{{ trans('plugins/url-shortener::qr-code.background_color') }}</label>
                                <div class="input-group">
                                    <input type="color" class="form-control form-control-color" id="qr-bgcolor-picker" value="#FFFFFF">
                                    <input type="text" class="form-control" id="qr-bgcolor" value="255-255-255" placeholder="255-255-255">
                                </div>
                                <small class="form-text text-muted">{{ trans('plugins/url-shortener::qr-code.color_format_help') }}</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="generate-qr-btn">
                                <i class="fas fa-sync"></i> {{ trans('plugins/url-shortener::qr-code.generate') }}
                            </button>
                            <button type="button" class="btn btn-success" id="download-qr-btn">
                                <i class="fas fa-download"></i> {{ trans('plugins/url-shortener::qr-code.download') }}
                            </button>
                            <button type="button" class="btn btn-warning" id="clear-cache-btn">
                                <i class="fas fa-trash"></i> {{ trans('plugins/url-shortener::qr-code.clear_cache') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/url-shortener::qr-code.preview') }}</h4>
                </div>
                <div class="card-body text-center">
                    <div id="qr-preview">
                        <img src="{{ $qrCodeUrl }}" alt="QR Code" class="img-fluid" id="qr-image" style="max-width: 100%; border: 1px solid #ddd; padding: 10px;">
                    </div>
                    <div class="mt-3">
                        <small class="text-muted">{{ trans('plugins/url-shortener::qr-code.scan_info') }}</small>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">{{ trans('plugins/url-shortener::qr-code.url_info') }}</h4>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">{{ trans('plugins/url-shortener::url-shortener.alias') }}:</dt>
                        <dd class="col-sm-7">{{ $urlShortener->short_url }}</dd>

                        <dt class="col-sm-5">{{ trans('plugins/url-shortener::url-shortener.target_url') }}:</dt>
                        <dd class="col-sm-7">
                            <a href="{{ $urlShortener->long_url }}" target="_blank" class="text-truncate d-block">
                                {{ Str::limit($urlShortener->long_url, 30) }}
                            </a>
                        </dd>

                        <dt class="col-sm-5">{{ trans('core/base::tables.status') }}:</dt>
                        <dd class="col-sm-7">{!! $urlShortener->status->toHtml() !!}</dd>

                        @if($urlShortener->expired_at)
                            <dt class="col-sm-5">{{ trans('plugins/url-shortener::url-shortener.expired_at') }}:</dt>
                            <dd class="col-sm-7">{{ $urlShortener->expired_at->format('Y-m-d H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    <script>
        'use strict';

        const shortUrl = '{{ $urlShortener->short_url }}';
        const generateUrl = '{{ route('url_shortener.qr-code.generate', $urlShortener->short_url) }}';
        const downloadBaseUrl = '{{ route('url_shortener.qr-code.download', $urlShortener->short_url) }}';
        const clearCacheUrl = '{{ route('url_shortener.qr-code.clear-cache', $urlShortener->short_url) }}';

        function hexToRgb(hex) {
            hex = hex.replace('#', '');
            const r = parseInt(hex.substr(0, 2), 16);
            const g = parseInt(hex.substr(2, 2), 16);
            const b = parseInt(hex.substr(4, 2), 16);
            return `${r}-${g}-${b}`;
        }

        function rgbToHex(rgb) {
            const parts = rgb.split('-');
            const r = parseInt(parts[0]).toString(16).padStart(2, '0');
            const g = parseInt(parts[1]).toString(16).padStart(2, '0');
            const b = parseInt(parts[2]).toString(16).padStart(2, '0');
            return `#${r}${g}${b}`;
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                Botble.showSuccess('{{ trans('plugins/url-shortener::qr-code.copied') }}');
            });
        }

        function getQrOptions() {
            return {
                size: $('#qr-size').val(),
                ecc: $('#qr-ecc').val(),
                format: $('#qr-format').val(),
                color: $('#qr-color').val(),
                bgcolor: $('#qr-bgcolor').val(),
                margin: $('#qr-margin').val(),
                qzone: 1
            };
        }

        function generateQrCode() {
            const options = getQrOptions();
            const $btn = $('#generate-qr-btn');
            const originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ trans('plugins/url-shortener::qr-code.generating') }}');

            $.ajax({
                url: generateUrl,
                method: 'POST',
                data: options,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.error === false && response.data.url) {
                        $('#qr-image').attr('src', response.data.url + '&t=' + Date.now());
                        Botble.showSuccess('{{ trans('plugins/url-shortener::qr-code.generated_successfully') }}');
                    } else {
                        Botble.showError(response.message || '{{ trans('plugins/url-shortener::qr-code.generation_failed') }}');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || '{{ trans('plugins/url-shortener::qr-code.generation_failed') }}';
                    Botble.showError(message);
                },
                complete: function() {
                    $btn.prop('disabled', false).html(originalText);
                }
            });
        }

        function downloadQrCode() {
            const options = getQrOptions();
            const params = new URLSearchParams(options);
            window.location.href = `${downloadBaseUrl}?${params.toString()}`;
            Botble.showSuccess('{{ trans('plugins/url-shortener::qr-code.download_started') }}');
        }

        function clearCache() {
            if (!confirm('{{ trans('plugins/url-shortener::qr-code.clear_cache_confirm') }}')) {
                return;
            }

            $.ajax({
                url: clearCacheUrl,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.error === false) {
                        Botble.showSuccess(response.message);
                        generateQrCode();
                    } else {
                        Botble.showError(response.message);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || '{{ trans('core/base::notices.error') }}';
                    Botble.showError(message);
                }
            });
        }

        $(document).ready(function() {
            $('#qr-color-picker').on('change', function() {
                $('#qr-color').val(hexToRgb($(this).val()));
            });

            $('#qr-bgcolor-picker').on('change', function() {
                $('#qr-bgcolor').val(hexToRgb($(this).val()));
            });

            $('#qr-color').on('change', function() {
                const rgb = $(this).val();
                if (/^\d{1,3}-\d{1,3}-\d{1,3}$/.test(rgb)) {
                    $('#qr-color-picker').val(rgbToHex(rgb));
                }
            });

            $('#qr-bgcolor').on('change', function() {
                const rgb = $(this).val();
                if (/^\d{1,3}-\d{1,3}-\d{1,3}$/.test(rgb)) {
                    $('#qr-bgcolor-picker').val(rgbToHex(rgb));
                }
            });

            $('#generate-qr-btn').on('click', generateQrCode);
            $('#download-qr-btn').on('click', downloadQrCode);
            $('#clear-cache-btn').on('click', clearCache);

            $('#qr-size, #qr-ecc, #qr-format').on('change', function() {
                generateQrCode();
            });
        });
    </script>
@endpush
