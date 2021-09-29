<?php

namespace App\Common;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\File;
use PhpParser\Node\Stmt\TryCatch;

class ABPStorage
{

    private $storage;
    private $driver;
    private $thmb_width;
    private $thmb_prefix = '';


    public function __construct($driver)
    {

        try {
            $this->storage = Storage::disk($driver);
        } catch (\Exception $e) {
            dd($e->message);
        }

        $this->driver = $driver;
        $this->thmb_prefix = "/thumbs/";
        $this->thmb_width = config('abp.thmb_width');
        return $this;
    }

    // сохраняем файл на ФС
    public function saveFile($f, $file_name)
    {
        // результат работы
        $res = null;
        // получим тип файла по mime
        $mime = \File::mimeType($f);
        switch ($mime) {
            case "image/jpeg":
            case "image/png":
            case "image/bmp": {
                    $type = "image";
                }
                break;
            default: {
                    $type = "document";
                }
        }
        // по алгоритму на каждый драйвер
        switch ($this->driver) {
            case 'local': {
                    // папка по типу файлов
                    $folder = $type;
                    // сохраним файл
                    $path = $this->storage->putFileAs($folder, $f, $file_name);
                    if ($path) {
                        // расширение файла
                        $extension = pathinfo($path, PATHINFO_EXTENSION);
                        // возвращаем массив
                        $res = [
                            "filename" => $file_name,
                            "uid" => $path,
                            "extension" => $extension,
                            "folder" => $folder
                        ];
                    }
                }
                break;
            case 'google': {
                    // google - сохраняем в корень
                    $folder = '';
                    // определим расширение файла
                    $extension = pathinfo($f, PATHINFO_EXTENSION);
                    // сохраняем на диске (в гугле имя файлу можно давать по описанию,
                    // если оно есть, но тогда не получится создавать корректный тамб на картинку в БД)
                    $filename = $this->storage->putFileAs($folder, $f, $file_name);
                    if ($filename) {
                        // нужно найти файл для генерации ссылки на него в программе
                        // без рекурсий
                        $recursive = false; // Get subdirectories also?
                        // получим список файлов
                        $contents = collect($this->storage->listContents($folder, $recursive));
                        // определим расширение файла
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);
                        // найдем наш файл
                        $file = $contents
                            ->where('type', '=', 'file')
                            ->where('filename', '=', pathinfo($filename, PATHINFO_FILENAME))
                            ->where('extension', '=', $extension)
                            ->first(); // there can be duplicate file names!
                        // создадим служебные элементы сервиса и разрешений
                        $service = $this->storage->getAdapter()->getService();
                        $permission = new \Google_Service_Drive_Permission();
                        // установим разрешения для генерации публичного урла и получения fileId
                        $permission->setRole('reader'); // чтение
                        // $permission->setRole('writer'); // запись
                        $permission->setType('anyone'); // всем
                        $permission->setAllowFileDiscovery(false); // отключаем поиск файла - доступ только по прямой ссылке
                        // применяем разрешения
                        $permissions = $service->permissions->create($file['basename'], $permission);
                        // получаем публичный урл
                        $path = $this->storage->url($file['path']); // https://drive.google.com/uc?id=15Pz2-ZOmT0tZcYzF82DJlXjL3qekjkab&export=media
                        // шаблон для извлечения fileId (uid)
                        $pattern = '/id=([\w_-]+)&?/'; // https://drive.google.com/uc?id=1dK_Uh4KdfvkWtOqh0JhcQ3TYnNZGskxq&export=media
                        preg_match($pattern, $path, $matches);
                        // если нашли файл_ид
                        if (count($matches) == 2) {
                            // добавим fileId  в поле uid
                            $res["uid"] = $matches[1];
                        }
                        $res["filename"] = $file_name;
                        $res["extension"] = $extension;
                        $res["folder"] = $folder;
                    }
                }
        }
        if ($res) {
            // если это изображение - сохраним тамб в БД (вне зависимости от облаков превью будет доступно)
            if ($type == "image") {
                $img = \Image::make($f);
                $img->resize($this->thmb_width, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
                $img->save(Storage::disk('local')->path($this->thmb_prefix) . $file_name, 50);
            }
        }
        return $res;
    }

    // копируем файл
    // TODO - не копируются файлы на Google Drive
    public function copy_file(File $file_model)
    {
        // ошибки
        $errors = [];
        // если файл существует
        if ($this->storage->has($file_model->uid)) {
            // это картинка
            $is_img = $this->is_image($file_model->uid);
            // сгенерим имя новому файлу
            $copied_file_name = (string) Str::uuid() . "." . $file_model->extension;
            // получим существующий файл
            switch ($this->driver) {
                case 'local': {
                        $copied_file_name_with_folder = $file_model->folder . "/" . $copied_file_name;
                        $res = $this->storage->copy($file_model->uid, $copied_file_name_with_folder);
                        $res_data = [
                            "folder" => $file_model->folder,
                            "filename" => $copied_file_name,
                            "uid" => $copied_file_name_with_folder,
                            "extension" => $file_model->extension
                        ];
                    }
                    break;
                case 'google': {
                        $res = $this->storage->copy($file_model->uid, $copied_file_name);
                        if ($res) {
                            $gfile = $this->find_google_file_by_name($copied_file_name);
                            $uid = $this->get_uid_google_file($gfile);
                            $res_data = [
                                "folder" => '',
                                "filename" => $copied_file_name,
                                "uid" => $uid,
                                "extension" => $file_model->extension
                            ];
                        }
                    }
                    break;
                default: {
                        $errors[] = "файловая система [" . $this->driver . "] не доступна для копирования";
                    }
            }
            // если не скопировано
            if (isset($res) && !$res) {
                $errors[] = "файл не скопирован";
            } else {
                // скопируем thmb для картинки
                if ($is_img) {
                    // создадим диск, если не локал
                    if ($this->driver == 'local') {
                        $disk = $this->storage;
                    } else {
                        $disk = Storage::disk('local');
                    }
                    // старый тамб
                    $old_thmb = $this->thmb_prefix . "/" . $file_model->filename;
                    // если тамб есть - копируем
                    if ($disk->has($old_thmb)) {
                        // новый тамб
                        $new_thmb = $this->thmb_prefix . "/" . $copied_file_name;
                        // копируем
                        $res_thmb = $disk->copy($old_thmb, $new_thmb);
                        if (!$res_thmb) {
                            $errors[] = "thumb не скопирован";
                        }
                    } else {
                        $errors[] = "thumb не обнаружен по исходному пути";
                    }
                }
            }
        } else {
            $errors[] = "Файл не обнаружен по исходному пути";
        }
        return [
            "is_error" => count($errors) > 0,
            "errors" => $errors,
            "data" => isset($res_data) ? $res_data : null
        ];
    }

    // удаляем файл с тамбами для картинок
    public function delete_file($file_name, $thumb = null)
    {
        // если есть файл
        if ($this->storage->has($file_name)) {
            // удаляем файл с диска
            if ($this->storage->delete($file_name)) {
                // если картинка - удалим тамб из локального стораджа
                if ($this->is_image($file_name) && $thumb) {
                    // имя файла тамба
                    $thumb_name = $this->thmb_prefix . $thumb;
                    // проверим есть ли тамб
                    if (Storage::disk('local')->has($thumb_name)) {
                        // удаляем
                        return Storage::disk('local')->delete($thumb_name);
                    }
                }
                return true;
            } else {
                return $this->driver == "google";
            }
        } else {
            return true;
        }
    }

    // переносим файл на другую ФС
    public function replace_to(File $file_model, $target_driver)
    {
        // если файл существует
        if ($this->storage->has($file_model->uid)) {
            // получим существующий файл
            $file_content = $this->storage->get($file_model->uid);
            // сгенерим имя временному файлу
            $tmp_file_name = (string) Str::uuid() . "." . $file_model->extension;
            // записываем во временный файл источник
            if (Storage::disk('local')->put($tmp_file_name, $file_content)) {
                // создадим целевую файловую систему
                $target_storage = new ABPStorage($target_driver);
                // сгенерим новое имя файла
                $file_name = (string) Str::uuid() . "." . $file_model->extension;
                // записываем новый файл на целевой ФС
                $res_new_file = $target_storage->saveFile(Storage::disk('local')->path($tmp_file_name), $file_name);
                // удаляем временный файл с ФС
                Storage::disk('local')->delete($tmp_file_name);
                // // если файл перенесся - удаляем ФС источника
                // if ($res_new_file) {
                //     $this->delete_file($file_model->uid, $file_model->filename);
                // }
                return $res_new_file;
            }
        }
        return false;
    }

    // выдаем превью файла
    public function file_preview(File $file_model)
    {
        // if ($this->storage && $this->storage->has($file_model->uid)) {
        //     $mime = $this->storage->mimeType($file_model->uid);
        switch ($file_model->extension) {
            case "jpeg":
            case "jpg":
            case "png":
            case "bmp": {
                    $disk = Storage::disk('local');
                    $thmb_path = $this->thmb_prefix . $file_model->filename;
                    if ($disk->has($thmb_path)) return asset($disk->url($thmb_path));
                }
                break;
        }
        // }
        return null;
    }

    // найти файл в гугле по имени
    public function find_google_file_by_name($filename)
    {
        $file_name_ext_array = explode('.', $filename);
        // расширение
        $ext = end($file_name_ext_array);
        // имя файла
        $file_name = substr($filename, 0, strlen($filename) - strlen($ext) - 1);
        // папка
        $folder = '';
        // опции поиска
        // без рекурсий
        $recursive = false; // Get subdirectories also?
        // получим список файлов
        $contents = collect($this->storage->listContents($folder, $recursive));
        // найдем наш файл
        $file = $contents
            ->where('type', '=', 'file')
            ->where('filename', '=', $file_name)
            ->where('extension', '=', $ext)
            ->first(); // there can be duplicate file names!
        return $file;
    }

    // получим uid файла google drive
    // здесь $file - экземпляр гуглового файла (можно получить методом find_google_file_by_name)
    public function get_uid_google_file($file)
    {
        $service = $this->storage->getAdapter()->getService();
        $permission = new \Google_Service_Drive_Permission();
        // установим разрешения для генерации публичного урла и получения fileId
        $permission->setRole('reader'); // чтение
        $permission->setType('anyone'); // всем
        $permission->setAllowFileDiscovery(false); // отключаем поиск файла - доступ только по прямой ссылке
        // применяем разрешения
        $permissions = $service->permissions->create($file['basename'], $permission);
        // // получаем публичный урл
        // $path = $this->storage->url($file['path']); // https://drive.google.com/uc?id=15Pz2-ZOmT0tZcYzF82DJlXjL3qekjkab&export=media
        // // шаблон для извлечения fileId (uid)
        // $pattern = '/id=([\w_-]+)&?/'; // https://drive.google.com/uc?id=1dK_Uh4KdfvkWtOqh0JhcQ3TYnNZGskxq&export=media
        // preg_match($pattern, $path, $matches);
        // // если нашли файл_ид
        // if (count($matches) == 2) {
        //     return $matches[1];
        // }
        return $file["path"];
    }

    // проверяем, картинка это ?
    public function is_image($file_name)
    {
        if ($this->storage->has($file_name)) {
            $mime = $this->storage->mimeType($file_name);
            $img_mimes = [
                "image/jpeg",
                "image/png",
                "image/bmp"
            ];
            return in_array($mime, $img_mimes);
        }
    }
}