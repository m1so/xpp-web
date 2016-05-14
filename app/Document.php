<?php

namespace App;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['title', 'folder'];

    protected $appends = ['files'];

    const ODE_FILE_NAME = 'input.ode';

    const RESULT_FILE_NAME = 'result.dat';

    const LOG_FILE_NAME = 'xpp.log';

    const NULLCLINES_FILE_NAME = 'nullclines.dat';

    const DIRFIELDS_FILE_NAME = 'dirfields.dat';

    const EQUILIBRIA_FILE_NAME = 'equil.dat';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private function getFileContent($name)
    {
        try {
            return \Storage::drive('local')->get(
                'xppweb'.DIRECTORY_SEPARATOR.$this->getKey().DIRECTORY_SEPARATOR.$name
            );
        } catch (FileNotFoundException $e) {
            return "";
        }
    }

    public function resultFile()
    {
        return $this->getFileContent(self::RESULT_FILE_NAME);
    }

    public function odeFile()
    {
        return $this->getFileContent(self::ODE_FILE_NAME);
    }

    public function logFile()
    {
        return $this->getFileContent(self::LOG_FILE_NAME);
    }

    public function nullclinesFile()
    {
        return $this->getFileContent(self::NULLCLINES_FILE_NAME);
    }

    public function directionFieldsFile()
    {
        return $this->getFileContent(self::DIRFIELDS_FILE_NAME);
    }

    public function equilibriaFile()
    {
        return $this->getFileContent(self::EQUILIBRIA_FILE_NAME);
    }

    // Accessors

    /**
     * Get all files for this document
     *
     * @return array
     */
    public function getFilesAttribute()
    {
        return [
            'ode' => $this->odeFile(),
            'result' => $this->resultFile(),
            'log' => $this->logFile(),
            'nullclines' => $this->nullclinesFile(),
            'directionField' => $this->directionFieldsFile(),
            'equilibria' => $this->equilibriaFile(),
        ];
    }
}
