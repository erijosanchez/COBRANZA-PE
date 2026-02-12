<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'debt_id',
        'installment_id',
        'debtor_id',
        'payment_method_id',
        'registered_by',
        'receipt_number',
        'amount',
        'payment_date',
        'reference',
        'notes',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    public function debtor()
    {
        return $this->belongsTo(Debtor::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereBetween('payment_date', [$from, $to]);
    }

    // Helpers
    public function getFormattedAmountAttribute(): string
    {
        $currency = $this->debt?->currency ?? 'PEN';
        $symbol = $currency === 'PEN' ? 'S/' : '$';
        return $symbol . ' ' . number_format($this->amount, 2);
    }

    public static function generateReceipt($companyId): string
    {
        $year = date('Y');
        $last = static::where('company_id', $companyId)
            ->where('receipt_number', 'like', "REC-{$year}-%")
            ->orderByDesc('receipt_number')
            ->first();

        $next = 1;
        if ($last) {
            $parts = explode('-', $last->receipt_number);
            $next = (int) end($parts) + 1;
        }

        return "REC-{$year}-" . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
