<?php

namespace ArchiElite\ShortUrl\Forms;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Forms\FormAbstract;
use ArchiElite\ShortUrl\Http\Requests\ShortUrlRequest;
use ArchiElite\ShortUrl\Models\ShortUrl;

class ShortUrlForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->setupModel(new ShortUrl())
            ->setValidatorClass(ShortUrlRequest::class)
            ->withCustomFields()
            ->add('long_url', 'text', [
                'label' => __('Target URL'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __('Ex: https://google.com'),
                    'data-counter' => 255,
                ],
            ])
            ->add('short_url', 'text', [
                'label' => __('Alias'),
                'label_attr' => ['class' => 'control-label required'],
                'attr' => [
                    'placeholder' => __('Ex: botble'),
                    'data-counter' => 15,
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
