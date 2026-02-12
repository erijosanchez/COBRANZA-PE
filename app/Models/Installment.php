<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Installment extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'number',
        'amount',
        'interest_amount',
        'penalty_amount',
        'total_amount',
        'paid_amount',
        'due_date',
        'paid_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_amount' => 'decimal:2',
        'penalty_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    // Relaciones
    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partial', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeByDebt($query, $debtId)
    {
        return $query->where('debt_id', $debtId);
    }

    // Helpers
    public function isOverdue(): bool
    {
        return $this->due_date->lt(now()) && in_array($this->status, ['pending', 'partial']);
    }

    public function getRemainingAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function recalculate(): void
    {
        $this->paid_amount = $this->payments()->where('status', 'confirmed')->sum('amount');

        if ($this->paid_amount >= $this->total_amount) {
            $this->status = 'paid';
            $this->paid_date = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = $this->isOverdue() ? 'overdue' : 'partial';
        } elseif ($this->isOverdue()) {
            $this->status = 'overdue';
        }

        $this->save();
    }
}
