<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
      protected $guarded = [];
      protected $casts = ['changes' => 'array'];

      public function causer() {
          return $this->belongsTo(User::class, 'user_id');
      }

      public function loggable() {
          return $this->morphTo();
      }    //
}
