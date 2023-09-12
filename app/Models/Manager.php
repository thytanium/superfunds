<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Manager extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name'];

    /**
     * Related Fund[] models.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Fund>>
     */
    public function funds(): HasMany
    {
        return $this->hasMany(Fund::class);
    }
}
