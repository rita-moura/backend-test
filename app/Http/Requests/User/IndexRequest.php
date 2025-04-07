<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Traits\SanitizesInput;

class IndexRequest extends FormRequest
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
            'name'   => 'sometimes',
            'email'  => 'sometimes',
            'status' => 'sometimes',
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
                'name'      => 'trim|strip_tags',
                'email'     => 'trim|lowercase',
                'status'    => 'trim|uppercase'
            ]
        ];
    }
    
}
