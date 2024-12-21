<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book_for_borrow_copy extends Model
{
    protected $guarded = ['id'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
    public function book_borrowings(): HasMany
    {
        return $this->hasMany(Book_borrowing::class, 'book_copy_id');
    }
    public function borrow_requests(): HasMany
    {
        return $this->hasMany(Borrow_request::class,'copy_id');
    }
}
