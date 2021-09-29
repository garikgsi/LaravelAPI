<?php

// настройки мягкого удаления записей
// добавляем удалившего юзера
// добавляем возможность изменения полей перед удалением через event deleting

namespace App\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

trait ABPSoftDeletes {
    use SoftDeletes;

    protected function runSoftDelete() {
        $query = $this->setKeysForSaveQuery($this->newModelQuery());

        $time = $this->freshTimestamp();

        $columns = [$this->getDeletedAtColumn() => $this->fromDateTime($time)];

        $this->{$this->getDeletedAtColumn()} = $time;

        if ($this->timestamps && ! is_null($this->getUpdatedAtColumn())) {
            $this->{$this->getUpdatedAtColumn()} = $time;

            $columns[$this->getUpdatedAtColumn()] = $this->fromDateTime($time);

            // добавим удалившего юзера
            if($user = Auth::user()){
                $columns['deleted_by'] = $user->id;
            }
        }

        // обновим столбцы в модели перед удалением, если изменены в обсервере
        $columns = array_merge($query->getModel()->getDirty(), $columns);

        $query->update($columns);

        $this->syncOriginalAttributes(array_keys($columns));
    }
}
