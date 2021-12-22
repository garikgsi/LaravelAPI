<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\UserInfo;
use App\Triggers\UserInfoObserver;

// use App\SkladRegister;
// use App\Triggers\SkladRegisterObserver;

use App\SkladReceive;
use App\Triggers\SkladReceiveObserver;
use App\SkladReceiveItem;
use App\Triggers\SkladReceiveItemObserver;

use App\SkladMove;
use App\Triggers\SkladMoveObserver;
use App\SkladMoveItem;
use App\Triggers\SkladMoveItemObserver;

use App\Production;
use App\Triggers\ProductionObserver;
use App\ProductionItem;
use App\Triggers\ProductionItemObserver;
use App\ProductionComponent;
use App\Triggers\ProductionComponentObserver;
use App\ProductionReplace;
use App\Triggers\ProductionReplaceObserver;

use App\Contract;
use App\Triggers\ContractObserver;
use App\Order;
use App\Triggers\OrderObserver;
use App\OrderItem;
use App\Triggers\OrderItemObserver;
use App\Invoice;
use App\Triggers\InvoiceObserver;
use App\InvoiceItem;
use App\Triggers\InvoiceItemObserver;
use App\Act;
use App\Triggers\ActObserver;
use App\ActItem;
use App\Triggers\ActItemObserver;

use App\SerialNum;
use App\SerialNumMove;
use App\Triggers\SerialNumObserver;
use App\Triggers\SerialNumMoveObserver;

use App\File;
use App\Triggers\FileObserver;

use App\Sotrudnik;
use App\Triggers\SotrudnikObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // информация пользователя
        UserInfo::observe(UserInfoObserver::class);
        // регистр накопления
        // SkladRegister::observe(SkladRegisterObserver::class);
        // поступления на склад
        SkladReceive::observe(SkladReceiveObserver::class);
        SkladReceiveItem::observe(SkladReceiveItemObserver::class);
        // перемещения по складам
        SkladMove::observe(SkladMoveObserver::class);
        SkladMoveItem::observe(SkladMoveItemObserver::class);
        // производство
        Production::observe(ProductionObserver::class);
        ProductionItem::observe(ProductionItemObserver::class);
        ProductionComponent::observe(ProductionComponentObserver::class);
        ProductionReplace::observe(ProductionReplaceObserver::class);
        // серийники
        SerialNum::observe(SerialNumObserver::class);
        SerialNumMove::observe(SerialNumMoveObserver::class);
        // файлы
        File::observe(FileObserver::class);
        // сотрудники
        Sotrudnik::observe(SotrudnikObserver::class);
        // договоры
        Contract::observe(ContractObserver::class);
        // заказы
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
        // счета
        Invoice::observe(InvoiceObserver::class);
        InvoiceItem::observe(InvoiceItemObserver::class);
        // накладные
        Act::observe(ActObserver::class);
        ActItem::observe(ActItemObserver::class);
    }
}