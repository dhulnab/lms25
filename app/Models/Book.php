<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Book extends Model
{
    protected $guarded = ['id'];

    public function first_category(): BelongsTo
    {
        return $this->belongsTo(First_category::class);
    }
    public function second_category(): BelongsTo
    {
        return $this->belongsTo(Second_category::class);
    }
    public function third_category(): BelongsTo
    {
        return $this->belongsTo(Third_category::class);
    }
    public function book_for_sell_copies(): HasMany
    {
        return $this->hasMany(Book_for_sell_copy::class);
    }
    public function book_for_borrow_copies(): HasMany
    {
        return $this->hasMany(Book_for_borrow_copy::class);
    }

    public function book_borrowings(): HasManyThrough
    {
        return $this->hasManyThrough(
            Book_borrowing::class,
            Book_for_borrow_copy::class,
            'book_id',
            'book_copy_id'
        );
    }
    public function borrow_requests(): HasManyThrough
    {
        return $this->hasManyThrough(
            Borrow_request::class,
            Book_for_borrow_copy::class,
            'book_id',
            'copy_id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
