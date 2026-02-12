<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

class Debt extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'debtor_id',
        'code',
        'concept',
        'description',
        'original_amount',
        'total_amount',
        'paid_amount',
        'pending_amount',
        'currency',
        'installments_count',
        'issue_date',
        'due_date',
        'interest_type',
        'interest_rate',
        'status',
        'days_overdue',
        'assigned_to',
        'notes',
    ];

    protected $casts = [
        'original_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'pending_amount' => 'decimal:2',
        'interest_rate' => 'decimal:4',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function debtor()
    {
        return $this->belongsTo(Debtor::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function collectionActions()
    {
        return $this->hasMany(CollectionAction::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    // Scopes
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'partial', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('code', 'like', "%{$search}%")
                ->orWhere('concept', 'like', "%{$search}%")
                ->orWhereHas('debtor', function ($dq) use ($search) {
                    $dq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                });
        });
    }

    // Helpers
    public function calculateDaysOverdue(): int
    {
        if ($this->status === 'paid' || $this->status === 'cancelled') {
            return 0;
        }

        $dueDate = $this->due_date;
        $today = Carbon::today();

        if ($today->gt($dueDate)) {
            return $today->diffInDays($dueDate);
        }

        return 0;
    }

    public function updateOverdueStatus(): void
    {
        $days = $this->calculateDaysOverdue();
        $this->days_overdue = $days;

        if ($days > 0 && in_array($this->status, ['active', 'partial'])) {
            $this->status = 'overdue';
        }

        $this->save();
    }

    public function calculateInterest(): float
    {
        if ($this->interest_type === 'none') {
            return 0;
        }

        $days = $this->calculateDaysOverdue();
        if ($days <= 0) return 0;

        $rate = $this->interest_rate / 100;

        return match ($this->interest_type) {
            'fixed' => $this->original_amount * $rate,
            'daily' => $this->pending_amount * $rate * $days,
            'monthly' => $this->pending_amount * $rate * ($days / 30),
            default => 0,
        };
    }

    public function recalculateAmounts(): void
    {
        $this->paid_amount = $this->payments()->where('status', 'confirmed')->sum('amount');
        $this->pending_amount = $this->total_amount - $this->paid_amount;

        if ($this->pending_amount <= 0) {
            $this->pending_amount = 0;
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = $this->calculateDaysOverdue() > 0 ? 'overdue' : 'partial';
        }

        $this->save();
    }

    public function getFormattedAmountAttribute(): string
    {
        $symbol = $this->currency === 'PEN' ? 'S/' : '$';
        return $symbol . ' ' . number_format($this->total_amount, 2);
    }

    public function getFormattedPendingAttribute(): string
    {
        $symbol = $this->currency === 'PEN' ? 'S/' : '$';
        return $symbol . ' ' . number_format($this->pending_amount, 2);
    }

    public static function generateCode($companyId): string
    {
        $year = date('Y');
        $last = static::where('company_id', $companyId)
            ->where('code', 'like', "DEB-{$year}-%")
            ->orderByDesc('code')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $parts = explode('-', $last->code);
            $nextNumber = (int) end($parts) + 1;
        }

        return "DEB-{$year}-" . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
