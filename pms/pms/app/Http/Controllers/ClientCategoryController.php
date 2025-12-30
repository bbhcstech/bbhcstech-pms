<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ClientCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Add this line to import DB facade
use Carbon\Carbon;

class ClientCategoryController extends Controller
{
    public function index()
        {
            return ClientCategory::all();
        }
        
         public function store(Request $request)
{
    $request->validate([
        'name' => 'required|unique:client_categories,name',
    ]);

    $category = ClientCategory::create([
        'name' => $request->name,
    ]);

    return response()->json($category);
}



}
