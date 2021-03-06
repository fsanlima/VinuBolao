<?php

namespace Bolao\Http\Controllers\API;

use Bolao\Models\Campeonato;
use Illuminate\Http\Request;
use Bolao\Http\Controllers\Controller;

class CampeonatoController extends Controller
{
    public function get($campeonatoId = null)
    {
        if($campeonatoId){
            return response()->json(Campeonato::findOrFail($campeonatoId));
        } else {
            return response()->json(Campeonato::all());
        }
    }
}
