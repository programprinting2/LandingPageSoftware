<?php

namespace App\Http\Requests;

use App\Models\PromoVoucher;
use App\Rules\ValidPromoVoucherCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminPromoVoucherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var PromoVoucher|null $voucher */
        $voucher = $this->route('promoVoucher');

        return [
            'code' => ['required', 'string', 'max:32', new ValidPromoVoucherCode($voucher?->id)],
            'label' => ['nullable', 'string', 'max:255'],
            'application_id' => ['nullable', 'integer', Rule::exists('applications', 'id')],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}
