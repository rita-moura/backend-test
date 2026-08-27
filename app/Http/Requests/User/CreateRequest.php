<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\SanitizesInput;

class CreateRequest extends FormRequest
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
            'document_number' => 'required|regex:/[0-9]{11}/i',
            'name'            => 'required',
            'email'           => 'required|email',
            'password'        => 'required',
            'type'            => 'required|in:USER,VIRTUAL,MANAGER'
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
                'document_number' => 'trim|digit',
                'name'            => 'trim|strip_tags',
                'email'           => 'trim|lowercase',
                'type'            => 'trim|uppercase'
            ]
        ];
    }
}
