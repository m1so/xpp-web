<?php

namespace App\Services;

use App\Document;
use App\Libraries\Xpp\Client;
use App\User;

class XppService
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $storage;

    public function __construct()
    {
        //$this->user = $user;

        $this->storage = \Storage::drive('local');
    }


    /**
     * @param string   $odeContent
     * @param Document $document
     *
     * @return Document
     *
     */
    public function runFromOde($odeContent, Document $document)
    {
        $path = 'xpp/'.$document->getKey();

        $this->storage->put($path.'/input.ode', $odeContent);

        // Run XPP client
        $client = new Client(storage_path('app/'.$path), base_path('xppaut8'));
        $client->run();

        // Return Document
        return $document;
    }
}
