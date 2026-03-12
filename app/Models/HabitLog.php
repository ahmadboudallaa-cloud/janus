<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitLog extends Model
{
    use HasFactory;

       
                        
       
    protected $fillable = [
        'habit_id',
        'completed_at',
        'note',
    ];

       
                                    
       
    protected function casts(): array
    {
        return [
            'completed_at' => 'date',
        ];
    }

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }
}
