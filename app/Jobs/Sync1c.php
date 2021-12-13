<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
// use Illuminate\Queue\SerializesModels;
// use Illuminate\Support\Facades\Notification;
use App\Notifications\Sync1CNotification;
use Carbon\Carbon;
use App\User;
use App\SkladReceive;

class Sync1c implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    // логи работы скрипта
    private $logs = [];
    // таймаут - время на исполнение скрипта 20 мин
    public $timeout = 1200;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->log("Начинаем экспорт в 1С");
        // переносим поступления

        $skald_receives = SkladReceive::where('is_active', 1)->whereNull('uuid')->get();
        $skald_receives_count = $skald_receives->count();
        if ($skald_receives_count > 0) {
            $this->log("Начинаем экспорт приходных накладных (всего " . $skald_receives_count . " шт)");
            foreach ($skald_receives as $sklad_receive) {
                $res = $sklad_receive->export_to_1c();
                if ($res["is_error"]) {
                    $this->log("Ошибка экспорта поступления " . $sklad_receive->select_list_title, $res["errors"]);
                } else {
                    $this->log("Поступление " . $sklad_receive->select_list_title . " экспортировано", $res["logs"]);
                }
            }
        } else {
            $this->log("Проведенных неэкспортированных приходных накладных нет. Пропускаем...");
        }

        $this->log("Экспорт в 1С завершен " . date('d.m.Y в H:i:s') . "(UTC)");

        $admins = User::where('is_admin', 1)->get();
        // Notification::send($email_user, new Sync1CNotification($logs));
        foreach ($admins as $email_user) {
            $email_user->notify((new Sync1CNotification($this->logs)));
        }
    }

    public function log($str, $arr_data = null)
    {
        $log = $str . ($arr_data ? ' ' . implode(",", $arr_data) : '');
        $this->logs[] = $log;
    }
}
