<?php

namespace App\Libraries\Xpp;

class Client
{
    /**
     * Absolute path to folder where files generated by XPPAut will be stored.
     *
     * @var string
     */
    protected $basePath;

    /**
     * Absolute path to XPPAut executable
     *
     * @var string
     */
    protected $xppPath;

    /**
     * Additional flags that will be passed to XPPAut's command
     *
     * @var string
     */
    protected $flags = "";

    /**
     * Client constructor.
     *
     * @param string $basePath
     * @param string $xppPath
     * @param string $flags
     */
    public function __construct($basePath, $xppPath, $flags = "")
    {
        $this->basePath = $basePath;
        $this->xppPath = $xppPath;
        $this->flags = $flags;
    }


    public function run()
    {
        $command = sprintf(
            "cd %s && %s %s -silent -outfile result.dat -logfile xpp.log %s",
            escapeshellarg($this->basePath),
            $this->xppPath,
            escapeshellarg($this->basePath.'/input.ode'),
            $this->flags
        );

        // We don't need output, since we are using silent flag and specifying log file path
        exec($command, $output, $status);

        \Log::debug($command, [$output, $status]);

        return $status;
    }

    public function withNullclines()
    {
        $this->flags .= " -ncdraw 2 ";
    }

    public function withDirectionField()
    {
        $this->flags .= " -dfdraw 4 ";
    }

}
