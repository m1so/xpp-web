<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateDocumentRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:128|min:5',
            'folder' => 'string|max:128',
            'ode' => 'mimetypes:text/plain|max:1024',
            'public' => 'boolean',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'ode.mimetypes' => 'Only text files are accepted as ODE.',
        ];
    }
}
