<?php

namespace App\Models;

use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    /** @use HasFactory<ApplicationFactory> */
    use HasFactory;

    /**
     * Nilai status yang diizinkan (sumber tunggal, dipakai validasi dan factory).
     */
    public const STATUSES = ['applied', 'interview'];

    protected $fillable = [
        'company',
        'position',
        'status',
        'deadline',
    ];

    /**
     * Nilai bawaan saat model dibuat tanpa status.
     */
    protected $attributes = [
        'status' => 'applied',
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'date:Y-m-d',
        ];
    }
}