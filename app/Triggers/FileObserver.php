<?php

namespace App\Triggers;

use App\File;
use App\FileDriver;
use App\Common\ABPStorage;

class FileObserver
{
    public function saving(File $f)
    {
        // старые значения
        $old = $f->getOriginal();
        // если изменяется месторасположение файла
        if (isset($old["file_driver_id"]) && $old["file_driver_id"] > 1 && $old["file_driver_id"] != $f->file_driver_id) {
            $old_driver = FileDriver::find($old["file_driver_id"]);
            $old_driver_name = $old_driver->name;
            $new_driver = FileDriver::find($f->file_driver_id);
            $new_driver_name = $new_driver->name;
            if ($old_driver_name && $new_driver_name) {
                $old_disk = new ABPStorage($old_driver_name);
                $res = $old_disk->replace_to($f, $new_driver_name);
                if ($res) {
                    $f->fill($res);
                } else {
                    abort(422, 'Не удалось перенести файл ' . $f->name . ' в ' . $new_driver->comment);
                    return false;
                }
            } else {
                abort(422, 'Не найдена исходная или конечная файловая система');
                return false;
            }
        }
    }

    public function saved(File $f)
    {
        // старые значения
        $old = $f->getOriginal();
        // если изменилось месторасположение файла
        if (isset($old["file_driver_id"]) && $old["file_driver_id"] > 1 && $old["file_driver_id"] != $f->file_driver_id) {
            $old_driver = FileDriver::find($old["file_driver_id"]);
            if ($old_driver) {
                $old_driver_name = $old_driver->name;
                $old_disk = new ABPStorage($old_driver_name);
                $old_disk->delete_file($old["uid"], $old["filename"]);
            }
        }
    }
}