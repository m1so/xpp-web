<?php

namespace App\Http\Controllers;

use App\Document;
use App\Services\XppService;
use Baumgartner\Xpp\Client;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class XppController extends Controller
{
    /** @var XppService */
    protected $xppService;

    /**
     * XppController constructor.
     *
     * @param XppService $xppService
     */
    public function __construct(XppService $xppService)
    {
        $this->xppService = $xppService;

        $this->middleware('auth');
    }


    public function run(Request $request)
    {
        $document = Document::findOrFail($request->get('id'));

        if ($document->user->id !== $request->user()->id) {
            return response()->json([
                'error' => 'Unauthorized'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $document = $this->xppService->runFromOde($request->get('ode'), $document);

        return [
            'output' => $document->resultFile(),
            'log' => $document->logFile(),
            'ode' => $document->odeFile(),
        ];
    }
}
