<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['sales_team_id', 'client_id', 'project_name', 'requirements', 'budget', 'deadline'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function salesTeam()
    {
        return $this->belongsTo(Team::class, 'sales_team_id');
    }
}
