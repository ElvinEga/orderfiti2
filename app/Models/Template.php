<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Template extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = "templates";
    protected $fillable = ['name', 'status'];
    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'status'      => 'integer',
    ];


    public function items() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TemplateItem::class)->where(['status' => Status::ACTIVE]);
    }
}
