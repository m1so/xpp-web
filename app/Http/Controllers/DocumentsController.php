<?php

namespace App\Http\Controllers;

use App\Document;
use App\Http\Requests\CreateDocumentRequest;
use App\Http\Requests\OwnsDocumentRequest;
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

        $this->middleware('auth');
    }

    /*
     * Show a single document
     */
    public function show(Request $request, $id)
    {
        /** @var Document $document */
        $document = Document::findOrFail($id);

        if ($document->user->id !== $request->user()->id) {
            return view('errors.401');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'data' => $document->toJson()
            ]);
        }

        return view('document.show', compact('document'));
    }

    /*
     * List all documents for current user
     */
    public function all(Request $request)
    {
        $documents = $request->user()->documents()->orderBy('folder')->get();

        return view('document.list', compact('documents'));
    }

    /*
     * Create a new document via API
     */
    public function create(CreateDocumentRequest $request)
    {
        $document = $this->documentService->create($request);

        return response()->json([
            'data' => $document,
            'message' => 'Document created'
        ], Response::HTTP_CREATED);
    }

    /*
     * Delete user's document via API
     */
    public function delete(OwnsDocumentRequest $request, $id)
    {
        $this->documentService->delete($id);

        return response()->json([
            'message' => 'Document deleted'
        ], Response::HTTP_OK);
    }

    /*
     * Updated user's document via API
     */
    public function update(OwnsDocumentRequest $request, $id)
    {
        $document = Document::find($id);
        $document->fill($request->all());
        $document->save();

        return response()->json([
            'data' => $document,
            'message' => 'Document updated'
        ], Response::HTTP_OK);
    }
}
