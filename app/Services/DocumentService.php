<?php

namespace App\Services;

use App\User;
use App\Document;
use Illuminate\Http\Request;
use Illuminate\Contracts\Filesystem\Filesystem;

class DocumentService
{
    const XPPWEB_DIRECTORY = 'xppweb';

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $storage;

    public function __construct(Filesystem $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Create a new document with provided ODE file
     *
     * @param Request $request
     *
     * @return Document
     */
    public function create(Request $request)
    {
        $document = $request->user()->documents()->create([
            'title' => $request->get('title', 'Untitled'),
            'folder' => $request->get('folder', '')
        ]);

        $path = self::XPPWEB_DIRECTORY.DIRECTORY_SEPARATOR.$document->getKey().DIRECTORY_SEPARATOR;
        $this->storage->makeDirectory($path);

        if ($request->hasFile('ode')) {
            $request->file('ode')->move(storage_path('app'.DIRECTORY_SEPARATOR.$path), 'input.ode');
        } else {
            $this->storage->put($path.'input.ode', '');
        }

        return $document;
    }

    /**
     * Delete Document by ID and all its files
     *
     * @param int $id
     */
    public function delete($id)
    {
        Document::destroy($id);

        $path = self::XPPWEB_DIRECTORY.DIRECTORY_SEPARATOR.$id;
        $this->storage->deleteDirectory($path);
    }
}
