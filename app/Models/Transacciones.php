<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

/**
 * @OA\Schema(
 *     schema="Transacciones",
 *     type="object",
 *     title="Transacciones",
 *     required={"id","user_id","monto","fecha"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="monto", type="number", format="float", example=150.5),
 *     @OA\Property(property="fecha", type="string", format="date-time", example="2025-10-03 15:30:00")
 * )
 */
class Transacciones extends Model
{
    protected $table = 'transacciones';

    protected $fillable = ['user_id', 'fecha', 'monto'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
