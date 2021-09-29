<?php

namespace App\Listeners;

use App\Events\SkladMoveIsOut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notification;
use App\Sklad;

class SendReceiveKeeperNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SkladMoveIsOut  $event
     * @return void
     */
    public function handle(SkladMoveIsOut $event)
    {
        // перемещение
        $sm = $event->sklad_move;
        // склад получения
        $sklad_in = Sklad::where('id', $sm->sklad_in_id)->first();
        // уведомление
        $n = new Notification;
        // данные уведомления
        $notification_data = [
            "name" => "Сформировано перемещение на Ваш склад",
            "text" => "На склад ".$sm->sklad_in." со склада ".$sm->sklad_out." было сформировано перемещение в количестве ".$sm->items()->count()." наименований",
            "user_id" => $sklad_in->keeper_id,
            "notification_type_id" => 2,
            "is_readed" => 0,
        ];
        // создаем уведомление
        $sm->notifications()->save($n->fill($notification_data));
    }
}
