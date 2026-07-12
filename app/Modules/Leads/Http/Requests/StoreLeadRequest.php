<?php

namespace App\Modules\Leads\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'required_without:phone', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'required_without:email', 'string', 'max:50'],
            'company' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'source' => ['required', 'string', Rule::in(array_keys(config('leads.sources', [])))],
            'page_url' => ['nullable', 'url:http,https', 'max:2048'],
            'metadata' => ['nullable', 'array:service,course,plan,budget,preferred_contact,campaign'],
            'metadata.service' => ['nullable', 'string', 'max:255'],
            'metadata.course' => ['nullable', 'string', 'max:255'],
            'metadata.plan' => ['nullable', 'string', 'max:255'],
            'metadata.budget' => ['nullable', 'string', 'max:255'],
            'metadata.preferred_contact' => ['nullable', 'string', 'max:100'],
            'metadata.campaign' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'max:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'email' => is_string($this->input('email')) ? trim($this->input('email')) : $this->input('email'),
            'phone' => is_string($this->input('phone')) ? trim($this->input('phone')) : $this->input('phone'),
            'source' => $this->input('source', 'contact_form'),
        ]);
    }
}
