<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'student_name',
    'student_number',
    'phone',
    'faculty',
    'study_program',
    'entry_year',
    'interests',
    'talents',
    'bio',
    'photo_path',
])]
class StudentProfile extends Model
{
    protected function casts(): array
    {
        return [
            'entry_year' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
