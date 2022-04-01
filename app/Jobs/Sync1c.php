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
use App\Act;
use App\Production;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class Sync1c implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    // логи работы скрипта
    private $logs = [];
    // таймаут - время на исполнение скрипта 20 мин
    public $timeout = 1200;

    /**
     * debuging mode - writo to log file
     *
     * @var bool
     */
    protected $debug = false;

    protected $user = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Auth::login($this->user);
        if ($this->debug) Log::info("Начинаем экспорт в 1С");
        $this->log("Начинаем экспорт в 1С");
        // переносим поступления
        $skald_receives = SkladReceive::where('is_active', 1)->whereNull('uuid')->get();
        $skald_receives_count = $skald_receives->count();
        if ($skald_receives_count > 0) {
            $this->log("Начинаем экспорт приходных накладных (всего " . $skald_receives_count . " шт)");
            if ($this->debug) Log::info("Начинаем экспорт приходных накладных (всего " . $skald_receives_count . " шт)");
            foreach ($skald_receives as $sklad_receive) {
                $res = $sklad_receive->export_to_1c();
                if ($res["is_error"]) {
                    $this->log("Ошибка экспорта поступления " . $sklad_receive->select_list_title, $res["errors"]);
                    if ($this->debug) Log::info("Ошибка экспорта поступления " . $sklad_receive->select_list_title, $res["errors"]);
                } else {
                    $this->log("Поступление " . $sklad_receive->select_list_title . " экспортировано", $res["logs"]);
                    if ($this->debug) Log::info("Поступление " . $sklad_receive->select_list_title . " экспортировано", $res["logs"]);
                }
            }
        } else {
            $this->log("Проведенных неэкспортированных приходных накладных нет. Пропускаем...");
            if ($this->debug) Log::info("Проведенных неэкспортированных приходных накладных нет. Пропускаем...");
        }
        // переносим производства
        $productions = Production::where('is_active', 1)->whereNull('uuid')->get();
        $productions_count = $productions->count();
        if ($productions_count > 0) {
            $this->log("Начинаем экспорт производств (всего " . $productions_count . " шт)");
            if ($this->debug) Log::info("Начинаем экспорт производств (всего " . $productions_count . " шт)");
            foreach ($productions as $production) {
                $res = $production->export_to_1c();
                if ($res["is_error"]) {
                    $this->log("Ошибка экспорта производства " . $production->select_list_title, $res["errors"]);
                    if ($this->debug) Log::info("Ошибка экспорта производства " . $production->select_list_title, $res["errors"]);
                } else {
                    $this->log("Производство " . $production->select_list_title . " экспортировано", $res["logs"]);
                    if ($this->debug) Log::info("Производство " . $production->select_list_title . " экспортировано", $res["logs"]);
                }
            }
        } else {
            $this->log("Проведенных неэкспортированных производств нет. Пропускаем...");
            if ($this->debug) Log::info("Проведенных неэкспортированных производств нет. Пропускаем...");
        }
        // переносим реализации
        $acts = Act::where('is_active', 1)->whereNull('uuid')->get();
        $acts_count = $acts->count();
        if ($acts_count > 0) {
            $this->log("Начинаем экспорт реализаций (всего " . $acts_count . " шт)");
            if ($this->debug) Log::info("Начинаем экспорт реализаций (всего " . $acts_count . " шт)");
            foreach ($acts as $act) {
                $res = $act->export_to_1c();
                if ($res["is_error"]) {
                    $this->log("Ошибка экспорта реализации " . $act->select_list_title, $res["errors"]);
                    if ($this->debug) Log::info("Ошибка экспорта реализации " . $act->select_list_title, $res["errors"]);
                } else {
                    $this->log("Реализация " . $act->select_list_title . " экспортировано", $res["logs"]);
                    if ($this->debug) Log::info("Реализация " . $act->select_list_title . " экспортировано", $res["logs"]);
                }
            }
        } else {
            $this->log("Проведенных неэкспортированных реализаций нет. Пропускаем...");
            if ($this->debug) Log::info("Проведенных неэкспортированных реализаций нет. Пропускаем...");
        }

        $this->log("Экспорт в 1С завершен " . date('d.m.Y в H:i:s') . "(UTC)");
        if ($this->debug) Log::info("Экспорт в 1С завершен " . date('d.m.Y в H:i:s') . "(UTC)");

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
