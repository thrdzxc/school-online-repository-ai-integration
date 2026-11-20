@extends('layouts.app')
@section('title','Repository Monitor')
@section('content')
<div class="page-head"><div><h1>Repository Monitor</h1><p>Supervisor view of files and text stored by students.</p></div></div>
<div class="card table-wrap"><table><thead><tr><th>Owner</th><th>Item</th><th>Subject</th><th>Type</th><th>Date</th><th></th></tr></thead><tbody>
@forelse($items as $item)<tr><td><strong>{{ $item->user->name }}</strong><br><span class="small muted">{{ $item->user->school_id }}</span></td><td><strong>{{ $item->title }}</strong><br><span class="small muted">{{ Str::limit($item->description,80) }}</span></td><td>{{ $item->subject?->name ?? '-' }}</td><td>{{ $item->file_path ? ($item->original_filename ?: 'File') : 'Text' }}</td><td>{{ $item->created_at->format('M d, Y') }}</td><td><a class="btn secondary sm" href="{{ route('admin.repository.show',$item) }}">Open</a></td></tr>@empty<tr><td colspan="6" class="empty">Repository is empty.</td></tr>@endforelse
</tbody></table>{{ $items->links('pagination.simple') }}</div>
@endsection
