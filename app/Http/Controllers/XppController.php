<?php

namespace App\Http\Controllers;

use App\Document;
use App\Services\XppService;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class XppController extends Controller
{
    /** @var XppService */
    protected $xpp;

    /**
     * XppController constructor.
     *
     * @param XppService $xppService
     */
    public function __construct(XppService $xppService)
    {
        $this->xpp = $xppService;

        $this->middleware('auth');
    }


    public function run(Request $request)
    {
        $document = Document::findOrFail($request->input('document.id'));

        // Check if the user owns the document
        if ($document->user->id !== $request->user()->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $document = $this->xpp->run(
            $document,
            $request->input('document.input'),
            $request->input('options')
        );

        return response()->json([
            'document' => $document->toArray()
        ]);
    }
}
