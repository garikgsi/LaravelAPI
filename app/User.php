<?php

namespace App;

// use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// use Tymon\JWTAuth\Contracts\JWTSubject;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Carbon\Carbon;
use Illuminate\Support\Str;




class User extends Authenticatable implements MustVerifyEmail
// class User extends Authenticatable implements MustVerifyEmail, JWTSubject
{
    use Notifiable, HasRoles;
    // use HasApiTokens, Notifiable, HasRoles;
    // use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'api_token', 'api_token_lifetime',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // читатели
    protected $appends = ['is_verified'];


    // Rest omitted for brevity

    // /**
    //  * Get the identifier that will be stored in the subject claim of the JWT.
    //  *
    //  * @return mixed
    //  */
    // public function getJWTIdentifier()
    // {
    //     return $this->getKey();
    // }

    // /**
    //  * Return a key value array, containing any custom claims to be added to the JWT.
    //  *
    //  * @return array
    //  */
    // public function getJWTCustomClaims()
    // {
    //     return [];
    // }

    public function info()
    {
        return $this->hasOne('App\UserInfo', 'user_id');
    }

    public function ui()
    {
        return $this->hasMany('App\UserInterface');
    }

    public function getIsVerifiedAttribute()
    {
        return is_null($this->email_verified_at) ? false : true;
    }

    // обновление токена
    public function token_create()
    {
        $token_lifetime = Carbon::now()->addMonth()->toDateTimeString();
        $token = Str::random(80);
        return $this->update(['api_token' => $token, 'api_token_lifetime' => $token_lifetime]);
    }
    // отзыв токена
    public function token_revoke()
    {
        $token_lifetime = Carbon::now()->toDateTimeString();
        return $this->update(['api_token_lifetime' => $token_lifetime]);
    }
}