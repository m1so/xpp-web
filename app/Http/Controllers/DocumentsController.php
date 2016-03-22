<?php

namespace App\Http\Controllers;

use App\Document;
use App\Services\DocumentService;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Http\Response;

class DocumentsController extends Controller
{
    protected $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

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

    public function create(Request $request)
    {
        $document = $this->documentService->create($request, $request->user());

        return response()->json([
            'data' => $document,
            'message' => 'Document created'
        ], Response::HTTP_CREATED);
    }
}
