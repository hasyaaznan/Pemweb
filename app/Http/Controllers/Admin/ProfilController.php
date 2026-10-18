<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

// L22 Profil Admin
class ProfilController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('admin.profil', ['user' => $request->user()]);
    }
}
