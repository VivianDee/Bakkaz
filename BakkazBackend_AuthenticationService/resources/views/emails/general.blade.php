@extends('emails.layout')

@section('content')
<h1>{{ $greeting ?? 'Hello' }} {{ $name ?? ' User'}},</h1>
<p>{{ $body }}</p>
@endsection