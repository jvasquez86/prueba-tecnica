<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Transacciones extends Model
{
    protected $table = 'transacciones';

    protected $fillable = ['user_id', 'fecha', 'monto'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
