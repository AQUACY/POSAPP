<?php

namespace App\Http\Requests\SuperAdmin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Form request for validating business data
 */
class BusinessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ];

        // Add validation rules for settings if they are provided
        if ($this->has('receipt_settings')) {
            $rules['receipt_settings'] = 'array';
            $rules['receipt_settings.header'] = 'nullable|string|max:255';
            $rules['receipt_settings.footer'] = 'nullable|string|max:255';
            $rules['receipt_settings.show_tax'] = 'boolean';
            $rules['receipt_settings.show_logo'] = 'boolean';
        }

        if ($this->has('report_settings')) {
            $rules['report_settings'] = 'array';
            $rules['report_settings.currency'] = 'nullable|string|size:3';
            $rules['report_settings.date_format'] = 'nullable|string|max:20';
            $rules['report_settings.time_format'] = 'nullable|string|max:20';
            $rules['report_settings.show_logo'] = 'boolean';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Business name is required',
            'address.required' => 'Business address is required',
            'phone.required' => 'Business phone number is required',
            'email.required' => 'Business email is required',
            'email.email' => 'Please provide a valid email address',
            'tax_id.max' => 'Tax ID cannot exceed 50 characters',
            'receipt_settings.array' => 'Receipt settings must be in valid format',
            'report_settings.array' => 'Report settings must be in valid format',
        ];
    }
} 