<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesktopClient extends Model
{
    protected $visible = [
        'id',
        'created_at',
        'size'
    ];

    protected $fillable = [
        'disk',
        'path',
        'size'
    ];

    public function getContent()
    {
        return \Storage::get($this->path);
    }
}