<?php

namespace App;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = ['title', 'path'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    private function getFileContent($name)
    {
        try {
            return \Storage::drive('local')->get('xpp'.DIRECTORY_SEPARATOR.$this->getKey().DIRECTORY_SEPARATOR.$name);
        } catch (FileNotFoundException $e) {
            return "";
        }
    }

    public function resultFile()
    {
        return $this->getFileContent('result.dat');
    }

    public function odeFile()
    {
        return $this->getFileContent('input.ode');
    }

    public function logFile()
    {
        return $this->getFileContent('xpp.log');
    }

    public function nullclinesFile()
    {
        return $this->getFileContent('nullclines.dat');
    }

    public function directionFieldsFile()
    {
        return $this->getFileContent('dirfields.dat');
    }
}
