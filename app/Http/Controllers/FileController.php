<?php

namespace App\Http\Controllers;

use App\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->hasFile('filename')) {
            // данные запроса
            $reached_data = $request->all();
            // имя драйвера файловой системы
            $driver = $reached_data["driver"];
            // различная логика загрузки файла для разных драйверов
            switch ($driver) {
                // локальное хранилище
                case "local": {
                    // сгенерим имя файла
                    $path = $request->file('filename')->store(
                        '', $driver
                    );
                    // расширение файла
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                } break;
                // для драйвера гугл диска
                case "google": {
                    // загружаем в корень
                    $dir = '/';
                    // генерим имя файла стораджем
                    $filename = $request->file('filename')->store(
                        $dir, $driver
                    );
                    // создаем элемент диска
                    $disk = Storage::disk('google');
                    // без рекурсий
                    $recursive = false; // Get subdirectories also?
                    // получим список файлов
                    $contents = collect($disk->listContents($dir, $recursive));
                    // определим расширение файла
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    // найдем в списке наш файл
                    $file = $contents
                        ->where('type', '=', 'file')
                        ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
                        ->where('extension', '=', $extension)
                        ->first(); // there can be duplicate file names!
// dd($file);
                    // создадим служебные элементы сервиса и разрешений
                    $service = $disk->getAdapter()->getService();
                    $permission = new \Google_Service_Drive_Permission();
                    // установим разрешения для генерации публичного урла и получения fileId
                    $permission->setRole('reader'); // чтение
                    $permission->setType('anyone'); // всем
                    $permission->setAllowFileDiscovery(false); // отключаем поиск файла - доступ только по прямой ссылке
                    // применяем разрешения
                    $permissions = $service->permissions->create($file['basename'], $permission);
                    // получаем публичный урл
                    $path = $disk->url($file['path']); // https://drive.google.com/uc?id=15Pz2-ZOmT0tZcYzF82DJlXjL3qekjkab&export=media
                    // шаблон для извлечения fileId
                    $pattern = '/id=([\w_-]+)&?/'; // https://drive.google.com/uc?id=1dK_Uh4KdfvkWtOqh0JhcQ3TYnNZGskxq&export=media
                    preg_match($pattern, $path, $matches);
                    // если нашли файл_ид
                    if (count($matches)==2) {
                        // добавим fileId  в поле uuid
                        $reached_data["uuid"] = $matches[1];
                    }
                    // сохраняем только имя файла
                    $path = $filename;
                }
            }
            // если есть расширение - добавим его в БД
            if (isset($extension)) $reached_data["extension"] = $extension;
            // если файл загружен - сохраним его в БД
            if ($path) {
                $reached_data["filename"] = $path;
                $f = new File();
                $f->fill($reached_data);
                $f->save();
            }
        }
        return redirect()->back();
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\File  $file
     * @return \Illuminate\Http\Response
     */
    public function destroy(File $file)
    {
        //
    }
}
