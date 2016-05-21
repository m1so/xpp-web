<?php

namespace App\Services;

use App\Document;
use App\Libraries\Xpp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

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
            'equilibria' => false,
            'with' => [],
        ], $options);

        $relativePath = 'xppweb'.DIRECTORY_SEPARATOR.$document->getKey();
        $path = storage_path('app'.DIRECTORY_SEPARATOR.$relativePath);

        $this->cleanDirectory($relativePath, $options);

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
            $withString = collect($options['with'])->reduce(function ($result, $item) {
                return $result.escapeshellcmd($item['key']).'='.escapeshellcmd($item['value']).';';
            }, '');

            // Remove last trailing ";"
            $client->with(rtrim($withString, ';'));
        }

        // If we want to get equilibria, we generally don't want to integrate
        // the ODE file with "-with" options provided, this means we have to
        // add "-noout", so XPP only generates equilibria file "equil.dat"
        if ($options['equilibria']) {
            $client->withEquilibria();
            $client->run(['noout' => true]);
            return;
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

    private function cleanDirectory($path, $options)
    {
        // Delete these files by default
        $filesToDelete = [
            Document::RESULT_FILE_NAME,
            Document::LOG_FILE_NAME,
            Document::NULLCLINES_FILE_NAME,
            Document::DIRFIELDS_FILE_NAME,
            Document::EQUILIBRIA_FILE_NAME,
        ];

        // Items in this array will not be deleted
        $filesToKeep = [];

        // If we run with equilibria, we want to keep these files from previous run since we only generate "equil.dat"
        if ($options['equilibria']) {
            $filesToKeep[] = Document::RESULT_FILE_NAME;
            $filesToKeep[] = Document::NULLCLINES_FILE_NAME;
            $filesToKeep[] = Document::DIRFIELDS_FILE_NAME;
        }

        // Perform deletion
        (new Collection($filesToDelete))->diff($filesToKeep)->each(function($file) use ($path) {
            $filePath = $path.DIRECTORY_SEPARATOR.$file;

            if ($this->storage->exists($filePath)) {
                $this->storage->delete($filePath);
            }
        });
    }
}
