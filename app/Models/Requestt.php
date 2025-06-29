<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Requestt extends Model
{  protected $table = 'requests';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function requestable()
    {
        return $this->morphTo();
    }
}
