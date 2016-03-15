<?php

namespace App\Http\Controllers;

use App\Document;
use Illuminate\Http\Request;

use App\Http\Requests;

class DocumentsController extends Controller
{
    public function show(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        if ($document->user->id !== $request->user()->id) {
            return view('errors.401');
        }

        return view('document.show', compact('document'));
    }

    public function all(Request $request)
    {
        $documents = $request->user()->documents;

        return view('document.list', compact('documents'));
    }
}
