<?php
namespace App\Http\Controllers;

use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view("index", [
            "application" => config("app.name"),
            "sprint" => "Sprint 0 - Developer Onboarding",
            "technology" => "PHP with Laravel",
            "version" => config("app.version"),
        ]);
    }
}
