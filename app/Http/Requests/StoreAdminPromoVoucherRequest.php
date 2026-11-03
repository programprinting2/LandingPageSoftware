<?php

namespace App\Http\Requests;

use App\Rules\ValidPromoVoucherCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdminPromoVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32', new ValidPromoVoucherCode()],
            'label' => ['nullable', 'string', 'max:255'],
            'application_id' => ['nullable', 'integer', Rule::exists('applications', 'id')],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
