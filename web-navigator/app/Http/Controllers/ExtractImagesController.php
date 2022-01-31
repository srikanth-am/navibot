<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExtractImagesController extends Controller
{
    //
    public function index()
    {
        return view("crawl_images");
    }
}