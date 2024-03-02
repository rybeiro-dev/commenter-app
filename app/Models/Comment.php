<?php
declare(strict_types=1);
namespace App\Models;

use App\Events\CommentCreatedEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = ['message', 'user_id'];

    protected $dispatchesEvents = ['created' => CommentCreatedEvent::class];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
