<?php

namespace App\Http\Requests;

use App\Support\Cart;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    public const COUNTRIES = [
        'GR' => 'Ελλάδα',
        'CY' => 'Κύπρος',
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
            'required' => 'Το πεδίο «:attribute» είναι υποχρεωτικό.',
            'email' => 'Το email δεν είναι έγκυρο.',
            'max' => 'Το πεδίο «:attribute» είναι πολύ μεγάλο.',
            'country.in' => 'Επιλέξτε μια διαθέσιμη χώρα.',
            'items.required' => 'Το καλάθι σας είναι άδειο.',
            'items.*' => 'Το καλάθι σας δεν είναι έγκυρο.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Ονοματεπώνυμο',
            'email' => 'Email',
            'phone' => 'Τηλέφωνο',
            'address' => 'Διεύθυνση',
            'city' => 'Πόλη',
            'postal_code' => 'Τ.Κ.',
            'country' => 'Χώρα',
            'notes' => 'Σημειώσεις',
        ];
    }
}
