<?php

namespace App\Services;

use App\Document;
use App\Libraries\Xpp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;

class XppService
{
    /**
     * @var \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected $storage;

    /**
     * @var string
     */
    protected $xppPath;

    public function __construct(Filesystem $storage)
    {
        $this->xppPath = config('xppweb.xpp_path');
        $this->storage = $storage;
    }

    public function run(Document $document, $input, $options)
    {
        // Options
        $options = array_replace([
            'nullclines' => false,
            'directionField' => false
        ], $options);

        $relativePath = 'xppweb/'.$document->getKey();
        $path = storage_path('app/'.$relativePath);

        $this->cleanDirectory($relativePath);

        $this->saveOdeFile($relativePath, $input);

        $this->runClient($path, $options);

        return $document;
    }

    /**
     * Run XPP client
     *
     * @param $path
     * @param $options
     */
    private function runClient($path, $options)
    {
        $client = new Client($path, $this->xppPath);

        if ($options['nullclines']) {
            $client->withNullclines();
        }

        if ($options['directionField']) {
            $client->withDirectionField();
        }

        $client->run();
    }

    /**
     * Save input file so we can run XPP
     *
     * @param $path
     * @param $input
     */
    private function saveOdeFile($path, $input)
    {
        $odePath = $path.DIRECTORY_SEPARATOR.Document::ODE_FILE_NAME;

        $this->storage->put($odePath, $input);
    }

    private function cleanDirectory($path)
    {
        // Remove all contents before new run and create a fresh directory
        $this->storage->deleteDirectory($path);
        $this->storage->makeDirectory($path);
    }
}
