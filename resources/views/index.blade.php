@extends('layouts.auth')

@section('content')

    @auth
        <form method="POST" action="{{route('logOut')}}">
            @csrf
            @method('DELETE')
            <button type="submit">Выйти</button>
        </form>
    @endauth

    @if(auth()->guest())
        <a href="/login">Войти</a>
    @endif

@endsection
