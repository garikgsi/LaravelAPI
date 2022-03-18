<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class Pdf
{
    // бинарник хрома
    protected $binary;

    public function __construct()
    {
        $this->binary = config('abp.chrome_bin');
    }

    // генерация pdf ($data - html-контент)
    public function render($data, $type = 'file')
    {
        if (!is_null($this->binary)) {
            // путь для сохранения файла
            $path = tempnam(sys_get_temp_dir(), Str::random());
            // запускаем конверртер в хроме
            $process = new Process([
                $this->binary,
                '--headless',
                '--disable-gpu',
                '--print-to-pdf-no-header',
                '--user-data-dir=/tmp',
                '--print-to-pdf=' . $path,
                'data:text/html,' . rawurlencode($data)
            ]);
            // dd($process);
            // выполняем процесс
            try {
                $process->mustRun();
                return $type == 'file' ? File::get($path) : $path;
            } catch (ProcessFailedException $exception) {
                // dd($exception);
                return false;
            }
        }
        return false;
    }

    // генерация pdf ($data - html-контент)
    public function from_html_form($url, $type = 'file')
    {
        if (!is_null($this->binary)) {
            // путь для сохранения файла
            $path = tempnam(sys_get_temp_dir(), Str::random());
            // запускаем конверртер в хроме
            $process = new Process([
                $this->binary,
                '--headless',
                '--disable-gpu',
                '--print-to-pdf-no-header',
                '--user-data-dir=/tmp',
                '--print-to-pdf=' . $path,
                $url
            ]);
            // dd($process);
            // выполняем процесс
            try {
                $process->mustRun();
                return $type == 'file' ? File::get($path) : $path;
            } catch (ProcessFailedException $exception) {
                // dd($exception);
                return false;
            }
        }
        return false;
    }
}