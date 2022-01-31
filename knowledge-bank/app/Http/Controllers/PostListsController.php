<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PostListsController extends Controller
{
    //
    public function index(){
        return view('posts');
    }
}
