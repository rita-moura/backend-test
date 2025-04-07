<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\SanitizesInput;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    use SanitizesInput;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'user_document_number'    => 'required|regex:/[0-9]{11}/i',
            'user_name'               => 'required',
            'company_document_number' => 'required|regex:/[0-9]{14}/i',
            'company_name'            => 'required',
            'email'                   => 'required|email',
            'password'                => 'required',
        ];
    }

    /**
     * Define os filtros de sanitização para cada campo
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            'before' => [
                'user_document_number'    => 'trim|digit',
                'user_name'               => 'trim|strip_tags',
                'company_document_number' => 'trim|digit',
                'company_name'            => 'trim|strip_tags',
                'email'                   => 'trim|lowercase',
            ]
        ];
    }
}
