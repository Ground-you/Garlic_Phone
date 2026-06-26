<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        return Inertia::render('Home', [
            'defaultNickname' => '멋진별명' . sprintf('%03d', rand(1, 999)),
            'modes' => [
                ['id' => 'normal', 'title' => '일반 모드', 'active' => true],
                ['id' => 'next1', 'title' => '추가 예정', 'active' => false],
                ['id' => 'next2', 'title' => '추가 예정', 'active' => false],
                ['id' => 'next3', 'title' => '추가 예정', 'active' => false],
            ]
        ]);
    }
}