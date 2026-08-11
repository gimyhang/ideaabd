<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        $books   = collect();
        $ebooks  = collect();
        $authors = collect();

        try {
            if (DB::getSchemaBuilder()->hasTable('books')) {
                $books = DB::table('books')->latest()->take(8)->get();
            }
            if (DB::getSchemaBuilder()->hasTable('ebooks')) {
                $ebooks = DB::table('ebooks')->latest()->take(4)->get();
            }
            if (DB::getSchemaBuilder()->hasTable('authors')) {
                $authors = DB::table('authors')->latest()->take(6)->get();
            }
        } catch (\Throwable) {}

        return view('frontend.home', compact('books', 'ebooks', 'authors'));
    }
}
