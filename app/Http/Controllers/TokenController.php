<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TokenController extends Controller
{
    public function setToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');
        session(['moodle_token' => $token]);


        if (session('moodle_token')) {
            Log::info('Token definido na sessão', ['token' => session('moodle_token')]);
            return redirect()->back()->with('success', 'Token definido com sucesso! Token: ' . session('moodle_token'));
        } else {
            Log::error('Falha ao definir o token na sessão.');
            return redirect()->back()->with('error', 'Falha ao definir o token.');
        }
    }

    public function clearToken(Request $request)
    {
        $request->session()->forget('moodle_token');

        Log::info('Token limpo da sessão');

        return redirect()->back()->with('success', 'Token limpo com sucesso!');
    }
}
