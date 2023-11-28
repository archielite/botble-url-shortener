<?php

namespace ArchiElite\UrlShortener\Http\Controllers;

use ArchiElite\UrlShortener\Tables\UrlShortenerTable;
use Botble\Base\Http\Actions\DeleteResourceAction;
use Botble\Base\Http\Controllers\BaseController;
use ArchiElite\UrlShortener\Forms\UrlShortenerForm;
use ArchiElite\UrlShortener\Http\Requests\UrlShortenerRequest;
use ArchiElite\UrlShortener\Models\UrlShortener;
use ArchiElite\UrlShortener\Repositories\Interfaces\UrlShortenerInterface;
use Illuminate\Support\Str;

class UrlShortenerController extends BaseController
{
    public function __construct(protected UrlShortenerInterface $shortUrlRepository)
    {
        $this
            ->breadcrumb()
            ->add(trans('plugins/url-shortener::url-shortener.name'), route('url_shortener.index'));
    }

    public function index(UrlShortenerTable $table)
    {
        $this->pageTitle(trans('plugins/url-shortener::url-shortener.name'));

        return $table->renderTable();
    }

    public function create()
    {
        $this->pageTitle(trans('plugins/url-shortener::url-shortener.create'));

        return UrlShortenerForm::create()->renderForm();
    }

    public function store(UrlShortenerRequest $request)
    {
        $form =  UrlShortenerForm::create();

        $form->saving(function (UrlShortenerForm $form) use ($request) {
            $shortUrl = $request->input('short_url');
            if (empty($shortUrl)) {
                do {
                    $shortUrl = Str::random(6);
                } while (UrlShortener::where('short_url', $shortUrl)->first());
            }

            $data = $form->getRequestData();
            $data['short_url'] = $shortUrl;
            $data['user_id'] = $request->user()->getKey();

            $form
                ->getModel()
                ->fill($data)
                ->save();
        });

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('url_shortener.index'))
            ->setNextUrl(route('url_shortener.edit', $form->getModel()->getKey()))
            ->setMessage(trans('core/base::notices.create_success_message'));
    }

    public function edit(UrlShortener $urlShortener)
    {
        $this->pageTitle(trans('plugins/url-shortener::url-shortener.edit', ['name' => $urlShortener->short_url]));

        return UrlShortenerForm::createFromModel($urlShortener)->renderForm();
    }

    public function update(UrlShortener $urlShortener, UrlShortenerRequest $request)
    {
        UrlShortenerForm::createFromModel($urlShortener)
            ->saving(function (UrlShortenerForm $form) use ($urlShortener, $request) {
                $shortUrl = $request->input('short_url');
                if (empty($shortUrl)) {
                    do {
                        $shortUrl = Str::random(6);
                    } while (UrlShortener::where('short_url', $shortUrl)->where('id', '!=', $urlShortener->id)->exists());
                }

                $data = $form->getRequestData();
                $data['short_url'] = $shortUrl;

                $form
                    ->getModel()
                    ->fill($data)
                    ->save();
        });

        return $this
            ->httpResponse()
            ->setPreviousUrl(route('url_shortener.index'))
            ->setMessage(trans('core/base::notices.update_success_message'));
    }

    public function destroy(UrlShortener $urlShortener)
    {
        return DeleteResourceAction::make($urlShortener);
    }
}
