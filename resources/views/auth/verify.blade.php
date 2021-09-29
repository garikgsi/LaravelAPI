@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                {{-- <div class="card-header">{{ __('Verify Your Email Address') }}</div> --}}
                <div class="card-header">Активируйте свою учетную запись на портале ООО "Мойдодыр"</div>
                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{-- {{ __('A fresh verification link has been sent to your email address.') }} --}}
                            Актуальная ссылка на активацию аккаунта вывслана на Ваш email
                        </div>
                    @endif

                    {{-- {{ __('Before proceeding, please check your email for a verification link.') }}
                    {{ __('If you did not receive the email') }}, --}}
                    <p>Чтобы работать с порталом необходимо подтвердить свой адрес электронной почты.</p>
                    <p>Проверьте свой почтовый ящик и перейдите по ссылке (письмо сожет попасть в SPAM).</p>
                    <p>Если письмо не дошло или ссылка уже просрочена - вы можете повторно сформировать актуальную ссылку для подтверждения аккаунта.</p>
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        {{-- <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>. --}}
                        <button type="submit" class="btn btn-link p-0 m-0 align-baseline">Повторить запрос</button>.
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
