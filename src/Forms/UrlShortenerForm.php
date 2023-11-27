<?php

namespace ArchiElite\UrlShortener\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use ArchiElite\UrlShortener\Http\Requests\UrlShortenerRequest;
use ArchiElite\UrlShortener\Models\UrlShortener;

class UrlShortenerForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new UrlShortener())
            ->setValidatorClass(UrlShortenerRequest::class)
            ->withCustomFields()
            ->add('short_url', 'text', [
                'label' => __('Alias'),
                'required' => true,
                'attr' => [
                    'placeholder' => __('Ex: botble'),
                    'data-counter' => 15,
                ],
            ])
            ->add('long_url', 'text', [
                'label' => __('Target URL'),
                'required' => true,
                'attr' => [
                    'placeholder' => __('Ex: https://google.com'),
                    'data-counter' => 255,
                ],
            ])
            ->add('status', 'customSelect', [
                'label' => trans('core/base::tables.status'),
                'label_attr' => ['class' => 'control-label required'],
                'choices' => BaseStatusEnum::labels(),
            ])
            ->setBreakFieldPoint('status');
    }
}
