<?php

namespace App\Http\Requests;

use App\Document;
use App\Http\Requests\Request;
use Auth;

class OwnsDocumentRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $documentId = $this->route('id');

        return Document::where('id', $documentId)->where('user_id', Auth::id())->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
