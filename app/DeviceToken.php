<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    protected $table = 'device_token';
    public $timestamps = false;
    protected $fillable = ['device_id', 'package_name', 'token', 'device_model', 'user', 'created_at'];
}
