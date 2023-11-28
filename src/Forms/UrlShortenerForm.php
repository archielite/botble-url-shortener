<?php

namespace ArchiElite\UrlShortener\Forms;

use ArchiElite\UrlShortener\Http\Requests\UrlShortenerRequest;
use ArchiElite\UrlShortener\Models\UrlShortener;
use Botble\Base\Forms\FieldOptions\StatusFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;

class UrlShortenerForm extends FormAbstract
{
    public function setup(): void
    {
        $this
            ->model(UrlShortener::class)
            ->setValidatorClass(UrlShortenerRequest::class)
            ->add(
                'short_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/url-shortener::url-shortener.alias'))
                    ->required()
                    ->maxLength(30)
                    ->placeholder(__('Ex: botble'))
                    ->toArray()
            )
            ->add(
                'long_url',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/url-shortener::url-shortener.target_url'))
                    ->required()
                    ->maxLength(255)
                    ->placeholder(__('Ex: https://google.com'))
                    ->toArray()
            )
            ->add('status', SelectField::class, StatusFieldOption::make()->toArray())
            ->setBreakFieldPoint('status');
    }
}
