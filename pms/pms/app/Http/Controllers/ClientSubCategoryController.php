<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\ClientSubCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Add this line to import DB facade
use Carbon\Carbon;

class ClientSubCategoryController extends Controller
{
    public function index()
    {
        return ClientSubCategory::with('category')->get();
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:client_sub_categories,name',
            'client_category_id' => 'required|exists:client_categories,id'
        ]);
        $sub = ClientSubCategory::create($request->only('name', 'client_category_id'));
        return response()->json($sub->load('category'));
    }
}
