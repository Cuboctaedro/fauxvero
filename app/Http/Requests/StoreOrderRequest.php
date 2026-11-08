<?php

namespace App\Http\Requests;

use App\Support\Cart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * Names are translation keys; translate them with __() when displaying.
     */
    public const COUNTRIES = [
        'GR' => 'Greece',
        'CY' => 'Cyprus',
    ];

    public function authorize(): bool
    {
        return true;
    }

    /**
     * The checkout form posts the localStorage cart as a JSON string.
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('items'))) {
            $this->merge(['items' => json_decode($this->input('items'), true)]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'postal_code' => ['required', 'string', 'max:10'],
            'country' => ['required', Rule::in(array_keys(self::COUNTRIES))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer'],
            'items.*.qty' => ['required', 'integer', 'min:1', 'max:'.Cart::MAX_QUANTITY],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => __('The :attribute field is required.'),
            'email' => __('The email address is not valid.'),
            'max' => __('The :attribute field is too long.'),
            'country.in' => __('Choose an available country.'),
            'items.required' => __('Your cart is empty.'),
            'items.*' => __('Your cart is not valid.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('Full name'),
            'email' => __('Email'),
            'phone' => __('Phone'),
            'address' => __('Address'),
            'city' => __('City'),
            'postal_code' => __('Postal code'),
            'country' => __('Country'),
            'notes' => __('Notes'),
        ];
    }
}
