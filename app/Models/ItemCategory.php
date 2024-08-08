<?php

namespace App\Models;

use App\Enums\Status;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ItemCategory extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = "item_categories";
    protected $fillable = ['name', 'slug', 'description', 'status', 'branch_id'];
    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'branch_id'   => 'integer',
        'slug'        => 'string',
        'description' => 'string',
        'status'      => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('item-category'))) {
            $category = $this->getMedia('item-category')->last();
            return $category->getUrl('thumb');
        }
        return asset('images/category/thumb.png');
    }

    public function getCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('item-category'))) {
            $category = $this->getMedia('item-category')->last();
            return $category->getUrl('cover');
        }
        return asset('images/category/cover.png');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 75, 48)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('cover')->width(400)->keepOriginalImageFormat()->sharpen(10);
    }

    public function items() : \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Item::class)->where(['status' => Status::ACTIVE]);
    }
}
