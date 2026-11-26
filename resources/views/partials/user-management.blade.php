<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span><i class="fas fa-users me-1"></i> User Management (Admin CRUD Controls)</span>
        <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">
            <i class="fas fa-user-plus me-1"></i> Add User
        </button>
    </div>
    <div class="card-body">
        @if (session('success'))
            <div class="alert alert-success" role="status">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="table-responsive">
            <table id="datatablesSimple" class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registration status</th>
                        <th>Registered at</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                                    {{ $user->role === 'admin' ? 'Admin' : 'Student' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $user->email_verified_at ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ $user->email_verified_at ? 'Registered' : 'Not registered' }}
                                </span>
                            </td>
                            <td>{{ $user->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="text-nowrap">
                                <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $user->id }}">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                @if ($user->id !== auth()->id())
                                    <form class="d-inline" method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete this account? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @foreach ($users as $user)
            <div class="modal fade" id="editUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="editUserModalLabel{{ $user->id }}" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('admin.users.update', $user) }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-header">
                                <h5 class="modal-title" id="editUserModalLabel{{ $user->id }}">Edit User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label" for="edit-name-{{ $user->id }}">Name</label>
                                    <input class="form-control" id="edit-name-{{ $user->id }}" name="name" value="{{ $user->name }}" required maxlength="255">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="edit-email-{{ $user->id }}">Email</label>
                                    <input class="form-control" id="edit-email-{{ $user->id }}" type="email" name="email" value="{{ $user->email }}" required maxlength="255">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="edit-role-{{ $user->id }}">Role</label>
                                    <select class="form-select" id="edit-role-{{ $user->id }}" name="role" required>
                                        <option value="student" @selected($user->role === 'student')>Student</option>
                                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="edit-password-{{ $user->id }}">New password <span class="text-muted">(leave blank to keep current)</span></label>
                                    <input class="form-control" id="edit-password-{{ $user->id }}" type="password" name="password" minlength="8" autocomplete="new-password">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="edit-password-confirmation-{{ $user->id }}">Confirm new password</label>
                                    <input class="form-control" id="edit-password-confirmation-{{ $user->id }}" type="password" name="password_confirmation" minlength="8" autocomplete="new-password">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">Create User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" for="create-name">Name</label>
                        <input class="form-control" id="create-name" name="name" value="{{ old('name') }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="create-email">Email</label>
                        <input class="form-control" id="create-email" type="email" name="email" value="{{ old('email') }}" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="create-role">Role</label>
                        <select class="form-select" id="create-role" name="role" required>
                            <option value="student" @selected(old('role', 'student') === 'student')>Student</option>
                            <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="create-password">Password</label>
                        <input class="form-control" id="create-password" type="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="create-password-confirmation">Confirm password</label>
                        <input class="form-control" id="create-password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create user</button>
                </div>
            </form>
        </div>
    </div>
</div>
