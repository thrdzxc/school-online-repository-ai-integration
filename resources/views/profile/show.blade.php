@extends('layouts.app')
@section('title','My Profile')
@section('topbar','My Profile')
@section('content')
<div class="page-head">
    <div>
        <h1>My Profile</h1>
        <p>Students, advisers, and administrators can upload their own profile picture. The image appears in the sidebar and account header.</p>
    </div>
</div>

<div class="grid grid-2">
    <div class="card profile-card">
        <div class="profile-hero">
            <div class="profile-avatar-lg" id="profilePreview">
                @if($user->profilePhotoUrl())
                    <img src="{{ $user->profilePhotoUrl() }}" alt="{{ $user->name }} profile photo" data-profile-preview>
                @else
                    <span data-profile-fallback>{{ strtoupper(substr($user->name,0,1)) }}</span>
                @endif
            </div>
            <div>
                <h3 style="margin-bottom:4px">{{ $user->name }}</h3>
                <p class="muted" style="margin:0">{{ ucfirst($user->role) }} · {{ $user->email }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="profile_photo">Profile Photo</label>
                <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp,image/gif" data-profile-input>
                <button class="btn secondary sm" type="button" id="open-camera">Open camera</button>
                <p class="small" id="camera-status" role="status"></p>
                <span class="small muted">JPG, PNG, WEBP, or GIF • maximum 4 MB.</span>
            </div>
            @if($user->profilePhotoUrl())
                <label class="inline small" style="margin-bottom:14px"><input type="checkbox" name="remove_photo" value="1"> Remove current photo instead</label>
            @endif
            <div class="actions">
                <button class="btn" type="submit"><x-icon name="camera" size="16" /> Save Profile Photo</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h3>Account Details</h3>
        <div class="score-line"><div><strong>Name</strong><br><span class="small muted">{{ $user->name }}</span></div></div>
        <div class="score-line"><div><strong>Email</strong><br><span class="small muted">{{ $user->email }}</span></div></div>
        <div class="score-line"><div><strong>Role</strong><br><span class="small muted">{{ ucfirst($user->role) }}</span></div></div>
        @if($user->school_id)<div class="score-line"><div><strong>School ID</strong><br><span class="small muted">{{ $user->school_id }}</span></div></div>@endif
        @if($user->course || $user->year_level || $user->section)
            <div class="score-line"><div><strong>Academic Information</strong><br><span class="small muted">{{ trim(($user->course ? $user->course.' ' : '').($user->year_level ? 'Year '.$user->year_level.' ' : '').($user->section ? 'Section '.$user->section : '')) }}</span></div></div>
        @endif
        @if($user->department)<div class="score-line"><div><strong>Department</strong><br><span class="small muted">{{ $user->department }}</span></div></div>@endif
    </div>
</div>
@endsection

@push('scripts')
<dialog id="photo-camera" class="camera-dialog" aria-labelledby="camera-title" aria-describedby="camera-instructions">
    <header class="camera-dialog-header">
        <span class="camera-heading-icon"><x-icon name="camera" size="24" /></span>
        <div><h2 id="camera-title">Take a Profile Photo</h2><p id="camera-instructions">Position yourself in the frame, then select Capture Photo.</p></div>
    </header>
    <div class="camera-dialog-body">
        <div class="camera-viewfinder">
            <video id="camera-video" autoplay playsinline muted aria-label="Live camera preview"></video>
            <img id="camera-preview" alt="Captured profile photo for review" hidden>
        </div>
        <p id="camera-live-status" class="camera-live-status" role="status" aria-live="polite">Preparing camera...</p>
        <p id="camera-error" role="alert"></p>
    </div>
    <footer class="camera-dialog-footer">
        <div class="camera-dialog-actions">
            <button type="button" class="btn secondary" id="cancel-camera">Cancel</button>
            <div class="camera-primary-actions">
                <button type="button" class="btn secondary" id="retake-photo" hidden>Retake Photo</button>
                <button type="button" class="btn camera-capture-button" id="capture-photo" disabled><x-icon name="camera" size="20" /> Capture Photo</button>
                <button type="button" class="btn" id="use-photo" hidden>Use This Photo</button>
            </div>
        </div>
        <p class="small muted">After confirming, select Save Profile Photo to save your changes.</p>
    </footer>
</dialog>
<script src="{{ asset('js/camera.js') }}?v={{ filemtime(public_path('js/camera.js')) }}" defer></script>
@endpush
