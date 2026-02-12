<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'company_id',
        'dni',
        'phone',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function collectionActions()
    {
        return $this->hasMany(CollectionAction::class);
    }

    public function assignedDebts()
    {
        return $this->hasMany(Debt::class, 'assigned_to');
    }

    public function collectionAssignments()
    {
        return $this->hasMany(CollectionAssignment::class);
    }

    public function activeAssignments()
    {
        return $this->collectionAssignments()->where('is_active', true);
    }

    public function paymentsRegistered()
    {
        return $this->hasMany(Payment::class, 'registered_by');
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

    // Helpers
    public function getFullInfoAttribute(): string
    {
        return $this->name . ($this->dni ? " ({$this->dni})" : '');
    }
}
