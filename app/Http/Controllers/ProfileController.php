<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_photo' => ['nullable', 'boolean'],
        ], [
            'profile_photo.image' => 'Please select a valid image file.',
            'profile_photo.mimes' => 'Profile photos must be JPG, JPEG, PNG, WEBP, or GIF.',
            'profile_photo.max' => 'Profile photos must not exceed 4 MB.',
        ]);

        if (!$request->hasFile('profile_photo') && !$request->boolean('remove_photo')) {
            throw ValidationException::withMessages(['profile_photo' => 'Choose a profile photo or select remove current photo.']);
        }

        if ($request->boolean('remove_photo') && !$request->hasFile('profile_photo')) {
            $this->deletePhoto($user->profile_photo_path);
            $user->profile_photo_path = null;
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $dir = public_path('uploads/profile-photos');
            if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw ValidationException::withMessages(['profile_photo' => 'The profile photo folder could not be created. Check folder permissions.']);
            }

            $extension = strtolower($file->extension() ?: 'jpg');
            $filename = Str::slug($user->name ?: 'user').'-'.$user->id.'-'.Str::lower(Str::random(10)).'.'.$extension;
            $oldPath = $user->profile_photo_path;
            $file->move($dir, $filename);
            $user->profile_photo_path = 'uploads/profile-photos/'.$filename;
            $this->deletePhoto($oldPath);
        }

        $user->save();
        return back()->with('success', 'Profile photo updated successfully.');
    }

    private function deletePhoto(?string $path): void
    {
        if (!$path || !str_starts_with($path, 'uploads/profile-photos/')) return;
        $absolute = public_path($path);
        if (is_file($absolute)) @unlink($absolute);
    }
}
