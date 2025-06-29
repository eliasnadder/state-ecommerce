<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{


    protected $fillable = [
    'owner_id',
    'owner_type',
    'name',
    'description',
    'location',
];
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }
  public function properties(): MorphMany
{
    return $this->morphMany(Property::class, 'owner');
}

    public function image()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function wantedProperties()
{
    return $this->morphMany(WantedProperty::class, 'wanted_Pable');
}
public function followers()
{
    return $this->belongsToMany(User::class, 'office_followers', 'office_id', 'user_id');
}
public function requests()
{
    return $this->morphMany(Request::class, 'requestable');
}
public function reviews()
{
    return $this->morphMany(Review::class, 'reviewable');
}

}
