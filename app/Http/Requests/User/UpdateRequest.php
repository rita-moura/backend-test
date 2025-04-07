<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\SanitizesInput;

class UpdateRequest extends FormRequest
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
            'name'     => 'sometimes|nullable',
            'email'    => 'sometimes|nullable|email',
            'password' => 'sometimes|nullable',
            'type'     => 'sometimes|nullable|in:USER,VIRTUAL,MANAGER'
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
                'name'  => 'trim|strip_tags',
                'email' => 'trim|lowercase',
                'type'  => 'trim|uppercase'
            ]
        ];
    }
}
