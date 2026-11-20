@extends('layouts.app')
@section('title','Edit Classroom')
@section('topbar','Edit Classroom')
@section('content')
@php
    $portal = auth()->user()->isAdmin() ? 'admin' : 'adviser';
    $backUrl = $portal === 'admin' ? route('admin.subjects') : route('adviser.rooms.index');
@endphp
<div class="page-head">
    <div><h1>Edit Classroom</h1><p>{{ $room->subject->name }} · {{ $room->name }} @if($room->section) · {{ $room->section }} @endif</p></div>
    <a class="btn secondary" href="{{ $backUrl }}">Back to Classrooms</a>
</div>
<div class="card">
    <p>Update the classroom name, section, physical room, schedule, or capacity. Existing enrollments, activities, and the enrollment key stay attached to this classroom.</p>
    <form method="POST" action="{{ route($portal.'.rooms.update', $room) }}">
        @csrf @method('PUT')
        @include('partials.room-edit-fields')
        <div class="actions"><button class="btn" type="submit">Save Classroom Changes</button><a class="btn secondary" href="{{ $backUrl }}">Cancel</a></div>
    </form>
</div>
@endsection
