<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class UserSession extends Model
{
    protected $table = 'sys_user_sessions';


    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id', 
        'user_id',
        'session_id',
        'login_tstamp',
        'logout_tstamp',
        'ip_address',
    ];


    

}
