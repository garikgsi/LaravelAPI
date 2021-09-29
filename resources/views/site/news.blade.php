@extends('layouts.app')

@section('content')
    <h1>Привет, я страница списка новостей</h1>
    <h2>{{ $request->fullUrl()}}</h2>
    <p>страница {{$request->input('page')}}</p>
@endsection
