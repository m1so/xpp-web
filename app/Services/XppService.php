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
            'directionField' => false,
            'with' => [],
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

        // Reduce array of key-values to single string in format: "key1=val1;key2=val2; ..."
        // and pass them to XPPAUT's -with flag
        if (!empty($options['with'])) {
            $withString = collect($options['with'])->reduce(function($result, $item) {
                return $result.escapeshellcmd($item['key']).'='.escapeshellcmd($item['value']).';';
            }, '');

            // Remove last trailing ";"
            $client->with(rtrim($withString, ';'));
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
