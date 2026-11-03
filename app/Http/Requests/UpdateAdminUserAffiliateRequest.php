<?php

namespace App\Http\Requests;

use App\Rules\ValidAffiliateReferralCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserAffiliateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'enabled' => ['required', 'boolean'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'suspended'])],
            'code' => [
                'sometimes',
                'nullable',
                'string',
                'max:32',
                new ValidAffiliateReferralCode($user?->affiliate?->id),
            ],
        ];
    }
}
