@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @if ($user->hasRole("super admin"))

                <div class="card">
                    <div class="card-header">Начало работы с БД</div>

                    <div class="card-body">
                        <p>Перенос справочников и остатков из старой БД</p>
                        <form method="POST" action="/move_remains">
                            @csrf
                            <button class="btn btn-success" type="submit">Выполнить перенос</button>
                        </form>
                        <p>Синхронизация с 1С</p>
                        <form method="POST" action="/sync1c">
                            @csrf
                            <button class="btn btn-success" type="submit">Синхронизация с 1С</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">Доступ к приложению</div>

                <div class="card-body">
                    <p>Теперь Вам доступно приложение ООО "Мойдодыр"</p>
                    <p>Скоро: выход приложения для Android и iOS</p>
                    <a class="btn btn-primary" target="_blank" href="https://db2.moydodyr.ru">Перейти к приложению</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
