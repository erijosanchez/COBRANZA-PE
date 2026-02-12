<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Debtor extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'document_type',
        'document_number',
        'full_name',
        'email',
        'phone',
        'phone_secondary',
        'address',
        'district',
        'province',
        'department',
        'reference',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function collectionActions()
    {
        return $this->hasMany(CollectionAction::class);
    }

    public function collectionAssignments()
    {
        return $this->hasMany(CollectionAssignment::class);
    }

    public function notificationLogs()
    {
        return $this->hasMany(NotificationLog::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('document_number', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Accessors
    public function getDocumentFullAttribute(): string
    {
        return "{$this->document_type}: {$this->document_number}";
    }

    public function getTotalDebtAttribute(): float
    {
        return $this->debts()->whereIn('status', ['active', 'partial', 'overdue'])->sum('pending_amount');
    }

    public function getActiveDebtsCountAttribute(): int
    {
        return $this->debts()->whereIn('status', ['active', 'partial', 'overdue'])->count();
    }
}
