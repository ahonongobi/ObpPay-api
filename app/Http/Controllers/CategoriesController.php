<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{

    public function index()
    {
        return response()->json(
            Categories::where('is_active', true)->orderBy('id')->get()
        );
    }
}
