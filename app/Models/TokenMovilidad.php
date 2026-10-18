<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenMovilidad extends Model
{
    protected $table = 'tokens_movilidad';
    protected $guarded = ['id'];
    protected $hidden = ['token_hash'];
    protected $casts = ['expires_at'=>'datetime', 'revoked_at'=>'datetime'];

    public function persona() { return $this->belongsTo(PersonaNoVidente::class, 'persona_id'); }
}
