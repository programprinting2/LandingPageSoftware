<?php

namespace App\Http\Requests;

use App\Support\AffiliateReferralCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateClientAffiliateCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

        $user = $this->user();
        $user?->loadMissing('affiliate');
        $affiliateId = $user?->affiliate?->id;
            $result = AffiliateReferralCode::validate($this->input('code'), $affiliateId);

            if (! $result['ok']) {
                $validator->errors()->add('code', $result['message'] ?? 'Kode tidak valid.');
            }
        });
    }
}
