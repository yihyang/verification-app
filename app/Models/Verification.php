<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Verification extends Model
{
    use HasFactory;

    const FILE_TYPE_JSON = 'JSON';

    protected $fillable = ['user_id', 'file_type', 'result'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
