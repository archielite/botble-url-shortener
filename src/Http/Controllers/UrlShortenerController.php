<?php

namespace ArchiElite\UrlShortener\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use ArchiElite\UrlShortener\Forms\UrlShortenerForm;
use ArchiElite\UrlShortener\Http\Requests\UrlShortenerRequest;
use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Repositories\Interfaces\UrlShortenerInterface;
use ArchiElite\UrlShortener\Tables\UrlShortenerTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UrlShortenerController extends BaseController
{
    public function __construct(protected UrlShortenerInterface $shortUrlRepository)
    {
    }

    public function index(UrlShortenerTable $table)
    {
        page_title()->setTitle(trans('plugins/url-shortener::url-shortener.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/url-shortener::url-shortener.create'));

        return $formBuilder->create(UrlShortenerForm::class)->renderForm();
    }

    public function store(UrlShortenerRequest $request, BaseHttpResponse $response)
    {
        $shortUrl = $request->input('short_url');
        if (empty($shortUrl)) {
            do {
                $shortUrl = Str::random(6);
            } while (UrlShortener::where('short_url', $shortUrl)->first());
        }

        $shortUrl = $this->shortUrlRepository->createOrUpdate([
            'long_url' => $request->input('long_url'),
            'short_url' => $shortUrl,
            'user_id' => $request->user()->getKey(),
            'status' => $request->input('status'),
        ]);

        event(new CreatedContentEvent(URL_SHORTENER_MODULE_SCREEN_NAME, $request, $shortUrl));

        return $response
            ->setPreviousUrl(route('url_shortener.index'))
            ->setNextUrl(route('url_shortener.edit', $shortUrl->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $shortUrl = $this->shortUrlRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $shortUrl));

        page_title()->setTitle(trans('plugins/url-shortener::url-shortener.edit', ['name' => $shortUrl->short_url]));

        return $formBuilder->create(UrlShortenerForm::class, ['model' => $shortUrl])->renderForm();
    }

    public function update($id, UrlShortenerRequest $request, BaseHttpResponse $response)
    {
        $url = $this->shortUrlRepository->findOrFail($id);

        $shortUrl = $request->input('short_url');
        if (empty($shortUrl)) {
            do {
                $shortUrl = Str::random(6);
            } while (UrlShortener::where('short_url', $shortUrl)->where('id', '!=', $url->id)->exists());
        }

        $url->fill([
            'long_url' => $request->input('long_url'),
            'short_url' => $shortUrl,
            'status' => $request->input('status'),
        ]);

        $this->shortUrlRepository->createOrUpdate($url);

        event(new UpdatedContentEvent(URL_SHORTENER_MODULE_SCREEN_NAME, $request, $url));

        return $response
            ->setPreviousUrl(route('url_shortener.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $shortUrl = $this->shortUrlRepository->findOrFail($id);

            $this->shortUrlRepository->delete($shortUrl);

            event(new DeletedContentEvent(URL_SHORTENER_MODULE_SCREEN_NAME, $request, $shortUrl));

            return $response->setMessage(trans('core/base::notices.delete_success_message'));
        } catch (Exception) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.cannot_delete'));
        }
    }

    public function deletes(Request $request, BaseHttpResponse $response)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $response
                ->setError()
                ->setMessage(trans('core/base::notices.no_select'));
        }

        foreach ($ids as $id) {
            $shortUrl = $this->shortUrlRepository->findOrFail($id);
            $this->shortUrlRepository->delete($shortUrl);
            event(new DeletedContentEvent(URL_SHORTENER_MODULE_SCREEN_NAME, $request, $shortUrl));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
