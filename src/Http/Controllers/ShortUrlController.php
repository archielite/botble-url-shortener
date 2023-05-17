<?php

namespace ArchiElite\ShortUrl\Http\Controllers;

use Botble\Base\Events\BeforeEditContentEvent;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Forms\FormBuilder;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use ArchiElite\ShortUrl\Forms\ShortUrlForm;
use ArchiElite\ShortUrl\Http\Requests\ShortUrlRequest;
use ArchiElite\ShortUrl\Models\ShortUrl;
use ArchiElite\ShortUrl\Repositories\Interfaces\ShortUrlInterface;
use ArchiElite\ShortUrl\Tables\ShortUrlTable;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShortUrlController extends BaseController
{
    public function __construct(protected ShortUrlInterface $shortUrlRepository)
    {
    }

    public function index(ShortUrlTable $table)
    {
        page_title()->setTitle(trans('plugins/short-url::short-url.name'));

        return $table->renderTable();
    }

    public function create(FormBuilder $formBuilder)
    {
        page_title()->setTitle(trans('plugins/short-url::short-url.create'));

        return $formBuilder->create(ShortUrlForm::class)->renderForm();
    }

    public function store(ShortUrlRequest $request, BaseHttpResponse $response)
    {
        $shortUrl = $request->input('short_url');
        if (empty($shortUrl)) {
            do {
                $shortUrl = Str::random(6);
            } while (ShortUrl::where('short_url', $shortUrl)->first());
        }

        $shortUrl = $this->shortUrlRepository->createOrUpdate([
            'long_url' => $request->input('long_url'),
            'short_url' => $shortUrl,
            'user_id' => $request->user()->getKey(),
            'status' => $request->input('status'),
        ]);

        event(new CreatedContentEvent(SHORT_URL_MODULE_SCREEN_NAME, $request, $shortUrl));

        return $response
            ->setPreviousUrl(route('short_url.index'))
            ->setNextUrl(route('short_url.edit', $shortUrl->id))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit($id, FormBuilder $formBuilder, Request $request)
    {
        $shortUrl = $this->shortUrlRepository->findOrFail($id);

        event(new BeforeEditContentEvent($request, $shortUrl));

        page_title()->setTitle(trans('plugins/short-url::short-url.edit') . ' "' . $shortUrl->short_url . '"');

        return $formBuilder->create(ShortUrlForm::class, ['model' => $shortUrl])->renderForm();
    }

    public function update($id, ShortUrlRequest $request, BaseHttpResponse $response)
    {
        $url = $this->shortUrlRepository->findOrFail($id);

        $shortUrl = $request->input('short_url');
        if (empty($shortUrl)) {
            do {
                $shortUrl = Str::random(6);
            } while (ShortUrl::where('short_url', $shortUrl)->where('id', '!=', $url->id)->first());
        }

        $url->fill([
            'long_url' => $request->input('long_url'),
            'short_url' => $shortUrl,
            'status' => $request->input('status'),
        ]);

        $this->shortUrlRepository->createOrUpdate($url);

        event(new UpdatedContentEvent(SHORT_URL_MODULE_SCREEN_NAME, $request, $url));

        return $response
            ->setPreviousUrl(route('short_url.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(Request $request, $id, BaseHttpResponse $response)
    {
        try {
            $shortUrl = $this->shortUrlRepository->findOrFail($id);

            $this->shortUrlRepository->delete($shortUrl);

            event(new DeletedContentEvent(SHORT_URL_MODULE_SCREEN_NAME, $request, $shortUrl));

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
            event(new DeletedContentEvent(SHORT_URL_MODULE_SCREEN_NAME, $request, $shortUrl));
        }

        return $response->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
