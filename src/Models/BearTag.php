<?php

namespace Michavie\Bearhub\Models;

use Illuminate\Database\Eloquent\Model;
use Michavie\Bearhub\Traits\ResolvesNoteTagPivot;
use Michavie\Bearhub\Traits\UsesBearsDatabaseConnection;

class BearTag extends Model
{
    use UsesBearsDatabaseConnection, ResolvesNoteTagPivot;

    public static function searchByTitle($query)
    {
        return static::where('title', 'like', '%'.$query.'%')->get();
    }

    public function notes()
    {
        return $this->belongsToMany(
            BearNote::class,
            $this->getNoteTagPivotTable(),
            $this->getTagColumn(),
            $this->getNoteColumn()
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(function ($builder) {
            $builder->fromRaw("(select Z_PK as id, ZTITLE as title, Z_ENT as pivot_column_id from ZSFNOTETAG) as bear_tags");
        });
    }
}
