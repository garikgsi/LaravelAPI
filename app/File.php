<?php

namespace App;

use App\ABPTable;
use App\FileDriver;
use Illuminate\Support\Facades\Storage;

class File extends ABPTable
{
    public function __construct()
    {
        parent::__construct();

        $this->table('files');

        // добавляем читателей
        $this->appends = array_merge($this->appends, ['url', 'preview', 'driver']);
        // преобразователи типов
        $this->casts = array_merge([
            'is_main' => 'boolean',
        ]);

        $this->model([
            ["name" => "file_driver_id", "type" => "select", "table" => "file_drivers", "table_class" => "FileDrivers", "title" => "Место хранения файла", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "file_type_id", "type" => "select", "table" => "file_types", "table_class" => "FileType", "title" => "Тип файла", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "folder", "type" => "string", "title" => "Папка хранения", "max" => 255, "require" => false, "index" => "index"],
            ["name" => "filename", "type" => "file", "title" => "Имя файла в ФС", "max" => 255, "require" => false, "index" => "index"],
            ["name" => "uid", "type" => "string", "title" => "Ссылка на файл в облаке", "max" => 1024, "require" => false, "index" => "index"],
            ["name" => "extension", "type" => "string", "title" => "Расширение файла", "max" => 5, "require" => false, "index" => "index"],
            ["name" => "is_main", "type" => "boolean", "title" => "Основное изображение", "require" => false, 'default' => false, "index" => "index"],
            // ["name"=>"file","type"=>"file","title"=>"Файл","require"=>true],
            ["name" => "table_id", "type" => "select", "table" => "polymorph", "title" => "ID владельца", "require" => true, "default" => 1, "index" => "index"],
            ["name" => "table_type", "type" => "polymorph_table", "title" => "таблица владельца", "require" => true, "index" => "index"],
        ]);
    }

    // читатели
    // выдаем url для полноценной работы
    public function getUrlAttribute()
    {
        if (isset($this->file_driver_id) && isset($this->attributes['uid'])) {
            $driver = FileDriver::find($this->file_driver_id);
            if ($driver) {
                $uid = $this->attributes['uid'];
                switch ($driver->name) {
                    case 'local': {
                            $file_name = asset('storage/' . $uid);
                        }
                        break;
                    case 'google': {
                            try {
                                $google_service = Storage::disk('google')->getAdapter()->getService();
                                if ($google_service) {
                                    $google_service = Storage::disk('google')->getAdapter()->getService();
                                    $gfile = $google_service->files->get($uid, ["fields" => "id, webContentLink, webViewLink"]);
                                    // $link = $gfile->getWebContentLink(); // для скачивания
                                    $link = $gfile->getWebViewLink(); // для просмотра
                                    // dd($gfile);
                                    $file_name = $link;
                                }
                            } catch (\Exception $e) {
                                // dd($e->message);
                            }
                        }
                        break;
                }
            }
        }
        return isset($file_name) ? $file_name : null;
    }

    // выдаем url для просмотра (картинка - превью, google-документ на чтение)
    public function getPreviewAttribute()
    {
        $file_name = '';
        if (isset($this->file_driver_id) && isset($this->attributes['extension'])) {
            $extension = strtolower($this->attributes["extension"]);
            // если картинка - выдаем превью из локального хранилища
            switch ($extension) {
                case "jpg":
                case "jpeg":
                case "png":
                case "bmp": {
                        $file_name = asset('storage/thumbs/' . $this->attributes["filename"]);
                    }
                    break;
                default: {
                        // документы
                        $driver = FileDriver::find($this->file_driver_id);
                        if ($driver) {
                            $uid = $this->attributes['uid'];
                            switch ($driver->name) {
                                case 'local': {
                                        $file_name = asset('storage/' . $uid);
                                        return $file_name;
                                    }
                                    break;
                                case 'google': {
                                        $file_name = "https://drive.google.com/file/d/" . $uid;
                                    }
                                    break;
                            }
                        }
                    }
            }
        }
        return $file_name;
    }

    // выдаем название драйвера
    public function getDriverAttribute()
    {
        $driver = null;
        if (isset($this->file_driver_id)) {
            $driver = FileDriver::find($this->file_driver_id);
        }
        return $driver ? $driver->name : '';
    }

    // вывдаем название драйвера
    public function driver()
    {
        return $this->belongsTo('App\FileDriver');
    }

    // делаем картинку главной
    public function make_main_image()
    {
        if (isset($this->attributes['id'])) {
            $this_file = $this;
            \DB::connection($this->connection())->transaction(function () use ($this_file) {
                $res = File::where('table_type', $this_file->attributes['table_type'])
                    ->where('table_id', $this_file->attributes['table_id'])
                    ->update(['is_main' => false]);
                $this_file->is_main = true;
                $res = $this_file->save();
            });
            return $this_file;
        } else {
            // dd('no id');
        }
        return false;
    }
}