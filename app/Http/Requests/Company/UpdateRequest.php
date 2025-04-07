<?php

namespace App\Http\Requests\Company;

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
            'name' => 'required',
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
                'name' => 'trim|strip_tags',
            ]
        ];
    }
}
