<?php

namespace App\Services;

use App\User;
use Illuminate\Http\Request;

class DocumentService
{
    protected $storage;

    public function __construct()
    {
        $this->storage = \Storage::drive('local');
    }

    public function create(Request $request, User $user)
    {
        $document = $user->documents()->create([
            'title' => $request->get('title', 'Untitled'),
        ]);

        $path = 'xppweb'.DIRECTORY_SEPARATOR.$document->getKey().DIRECTORY_SEPARATOR;
        $this->storage->makeDirectory($path);
        $this->storage->put($path.'input.ode', $request->get('ode', ''));

        return $document;
    }
}
