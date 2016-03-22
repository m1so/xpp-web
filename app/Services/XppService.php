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
     * @param bool     $nullclinesAndDirectionField
     *
     * @return Document
     */
    public function runFromOde($odeContent, Document $document, $nullclinesAndDirectionField = false)
    {
        $path = 'xpp/'.$document->getKey();

        $this->storage->put($path.'/input.ode', $odeContent);

        // Run XPP client
        $client = new Client(storage_path('app/'.$path), base_path('xppaut8'));
        if ($nullclinesAndDirectionField) {
            $client->withDirectionField();
            $client->withNullclines();
        }
        $client->run();

        // Return Document
        return $document;
    }
}
