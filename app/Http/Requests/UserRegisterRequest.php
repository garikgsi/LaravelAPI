<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'regex:/^[\w-]+(\.[\w-]+)*@moydodyr.ru$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    // Сообщения об ошибках

    public function messages()
    {
        return [
            'name.required' => 'Заполните свое имя',
            'email.regex' => 'Вы не можете регистрироваться с этого домена, используйте корпоративную почту',
            'password.min'  => 'Пароль должен содержать не мении 8 символов',
            'password.required'  => 'Пароль необходим для регистрации',
            'password.confirmed'  => 'Пароль не соответствует подтверждению',
        ];
    }
}