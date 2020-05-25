@extends('layouts.layout', ['title' => '404 ошибка'])

@section('content')
    <div class="card">
        <div class="card-header"><h2>Ошибка 404</h2></div>
        <div class="card-body">
            <img class="w-25" src="{{ asset('img/404.jpg') }}" alt="404">
            <div class="card-btn">
                <a href="{{ route('post.index') }}" class="btn btn-outline-primary">Вернуться главную</a>
            </div>
        </div>
    </div>

@endsection