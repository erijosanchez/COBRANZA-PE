<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'debt_id',
        'debtor_id',
        'user_id',
        'type',
        'result',
        'action_date',
        'action_time',
        'promise_date',
        'promise_amount',
        'notes',
    ];

    protected $casts = [
        'action_date' => 'date',
        'promise_date' => 'date',
        'promise_amount' => 'decimal:2',
    ];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }

    public function debtor()
    {
        return $this->belongsTo(Debtor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByDebt($query, $debtId)
    {
        return $query->where('debt_id', $debtId);
    }

    public function scopePromises($query)
    {
        return $query->where('result', 'promise_to_pay')->whereNotNull('promise_date');
    }

    public function scopePendingPromises($query)
    {
        return $query->promises()->where('promise_date', '>=', now());
    }

    // Helpers
    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'phone_call' => 'Llamada telefónica',
            'whatsapp' => 'WhatsApp',
            'email' => 'Correo electrónico',
            'visit' => 'Visita presencial',
            'letter' => 'Carta',
            'legal_notice' => 'Notificación legal',
            'promise_to_pay' => 'Promesa de pago',
            'other' => 'Otro',
            default => $this->type,
        };
    }

    public function getResultLabelAttribute(): string
    {
        return match ($this->result) {
            'contacted' => 'Contactado',
            'no_answer' => 'No contesta',
            'promise_to_pay' => 'Promesa de pago',
            'refused' => 'Se rehúsa',
            'wrong_number' => 'Número equivocado',
            'scheduled' => 'Reprogramado',
            'other' => 'Otro',
            default => $this->result,
        };
    }
}
