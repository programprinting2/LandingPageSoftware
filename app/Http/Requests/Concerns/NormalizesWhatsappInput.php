<?php

namespace App\Http\Requests\Concerns;

use App\Support\WhatsappNumber;

trait NormalizesWhatsappInput
{
    protected function prepareForValidation(): void
    {
        if (! $this->has('whatsapp')) {
            return;
        }

        $this->merge([
            'whatsapp' => WhatsappNumber::normalize($this->input('whatsapp')),
        ]);
    }
}
