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
                [
                    'id' => 'normal',
                    'title' => '일반 모드',
                    'desc' => '기본적인 이어그리고 맞추기 모드입니다.',
                    'image' => '/images/normal_mode.png',
                    'active' => true,
                ],
                [
                    'id' => '',
                    'title' => '추가 예정',
                    'desc' => '처음 만난 사람들과 친해지는 모드',
                    'image' => '',
                    'active' => false,
                ],
                [
                    'id' => '',
                    'title' => '추가 예정',
                    'desc' => '점수가 낮으면 탈락합니다.',
                    'image' => '',
                    'active' => false,
                ],
            ]
        ]);
    }
}