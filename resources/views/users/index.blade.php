@extends('layouts.app')

@section('title', 'Manage Users - Techcare')

@section('content')
    <div class="app-page-title">
        <div class="page-title-wrapper">
            <div class="page-title-heading">
                <div class="page-title-icon"><i class="pe-7s-users icon-gradient bg-mean-fruit"></i></div>
                <div>
                    Manage Users
                    <div class="page-title-subheading">View, add, edit, and delete user accounts. Admin only.</div>
                </div>
            </div>
            <div class="page-title-actions">
                <a href="{{ route('users.create') }}" class="tc-btn tc-btn-accent"><i class="fa fa-plus"></i> Add user</a>
            </div>
        </div>
    </div>

    @foreach (['success' => 'success', 'error' => 'danger'] as $key => $type)
        @if (session($key))
            <div class="alert alert-{{ $type }} alert-dismissible fade show" role="alert">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="row g-2 mb-3">
                <div class="col-md-6">
                    <input type="search" name="q" value="{{ request('q') }}" class="form-control"
                           placeholder="Search name, email, or phone">
                </div>
                <div class="col-md-3">
                    <select name="role" class="form-select">
                        <option value="">All roles</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role === 'teknisi' ? 'technician' : $role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="tc-btn tc-btn-primary" type="submit">Filter</button>
                    @if (request()->hasAny(['q', 'role']))
                        <a href="{{ route('users.index') }}" class="tc-btn tc-btn-outline">Reset</a>
                    @endif
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $u)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $u->avatarUrl() }}" alt="" class="rounded-circle me-3"
                                             style="width:40px;height:40px;object-fit:cover">
                                        <div>
                                            <div class="fw-semibold">
                                                {{ $u->name }}
                                                @if ($u->is(auth()->user()))<span class="tc-badge tc-badge-accent ms-1">You</span>@endif
                                            </div>
                                            <div class="text-muted small">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $u->phone ?: '-' }}</td>
                                <td>
                                    <span class="tc-badge {{ $u->role === 'admin' ? 'tc-badge-dark' : ($u->role === 'teknisi' ? 'tc-badge-accent' : 'tc-badge-success') }}">
                                        {{ $u->roleLabel() }}
                                    </span>
                                </td>
                                <td>{{ $u->created_at->format('d M Y') }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-pen"></i> Edit
                                    </a>
                                    @unless ($u->is(auth()->user()))
                                        <form method="POST" action="{{ route('users.destroy', $u) }}" class="d-inline"
                                              onsubmit="return confirm('Delete {{ e($u->name) }}? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $users->firstItem() ?? 0 }}-{{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                </div>
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
