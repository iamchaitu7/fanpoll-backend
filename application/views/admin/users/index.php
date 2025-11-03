<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Manage Users</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('zbt_admin/') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="<?php echo base_url('zbt_admin/home/users') ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Search Users</label>
                                    <input type="text" class="form-control" name="search"
                                        value="<?php echo htmlspecialchars($search ?? '') ?>"
                                        placeholder="Search by name or email...">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="mdi mdi-magnify"></i> Search
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div class="d-grid">
                                        <a href="<?php echo base_url('zbt_admin/home/users') ?>" class="btn btn-secondary">
                                            <i class="mdi mdi-refresh"></i> Reset
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                Users List
                                <span class="badge bg-primary ms-2"><?php echo $total_users ?> Total</span>
                            </h4>
                        </div>

                        <?php if (!empty($users)): ?>
                            <div class="table-responsive">
                                <table class="table table-nowrap table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>User Details</th>
                                            <th>Auth Method</th>
                                            <th>Activity Stats</th>
                                            <th>Social Stats</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th>Last Active</th>
                                            <!-- <th>Actions</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo $user->id ?></strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm me-3">
                                                            <img src="<?php echo base_url('uploads/profile_pictures/' . ($user->profile_picture ?: 'default.png')) ?>"
                                                                alt="" class="img-fluid rounded-circle">
                                                        </div>
                                                        <div>
                                                            <h5 class="font-size-14 mb-1"><?php echo $user->full_name ?></h5>
                                                            <p class="text-muted mb-0 font-size-12"><?php echo $user->email ?></p>
                                                            <?php if ($user->bio): ?>
                                                                <p class="text-muted mb-0 font-size-11">
                                                                    <?php echo character_limiter($user->bio, 50) ?>
                                                                </p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $auth_class = '';
                                                    $auth_icon = '';
                                                    switch ($user->auth_method) {
                                                        case 'email':
                                                            $auth_class = 'bg-primary';
                                                            $auth_icon = 'mdi-email';
                                                            break;
                                                        case 'google':
                                                            $auth_class = 'bg-danger';
                                                            $auth_icon = 'mdi-google';
                                                            break;
                                                        case 'apple':
                                                            $auth_class = 'bg-dark';
                                                            $auth_icon = 'mdi-apple';
                                                            break;
                                                        default:
                                                            $auth_class = 'bg-secondary';
                                                            $auth_icon = 'mdi-account';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $auth_class ?> font-size-12">
                                                        <i class="mdi <?php echo $auth_icon ?> me-1"></i>
                                                        <?php echo ucfirst($user->auth_method) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-center">
                                                        <h6 class="mb-1"><?php echo $user->polls_created_count ?? 0 ?></h6>
                                                        <p class="text-muted mb-0 font-size-12">Polls Created</p>
                                                        <h6 class="mb-1 mt-2"><?php echo $user->total_votes_cast ?? 0 ?></h6>
                                                        <p class="text-muted mb-0 font-size-12">Votes Cast</p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-center">
                                                        <h6 class="mb-1"><?php echo $user->followers_count ?? 0 ?></h6>
                                                        <p class="text-muted mb-0 font-size-12">Followers</p>
                                                        <h6 class="mb-1 mt-2"><?php echo $user->following_count ?? 0 ?></h6>
                                                        <p class="text-muted mb-0 font-size-12">Following</p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($user->status) {
                                                        case 'active':
                                                            $status_class = 'bg-success';
                                                            break;
                                                        case 'inactive':
                                                            $status_class = 'bg-secondary';
                                                            break;
                                                        case 'suspended':
                                                            $status_class = 'bg-warning';
                                                            break;
                                                        default:
                                                            $status_class = 'bg-light text-dark';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class ?> font-size-12">
                                                        <?php echo ucfirst($user->status) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="font-size-12">
                                                        <?php echo date('M j, Y', strtotime($user->created_at)) ?><br>
                                                        <span class="text-muted"><?php echo date('g:i A', strtotime($user->created_at)) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="font-size-12">
                                                        <?php if ($user->updated_at): ?>
                                                            <?php echo date('M j, Y', strtotime($user->updated_at)) ?><br>
                                                            <span class="text-muted"><?php echo date('g:i A', strtotime($user->updated_at)) ?></span>
                                                        <?php else: ?>
                                                            <span class="text-muted">Never</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                               
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <ul class="pagination pagination-rounded justify-content-center mt-4">
                                            <?php if ($current_page > 1): ?>
                                                <li class="page-item">
                                                    <a href="<?php echo base_url('zbt_admin/home/users?page=' . ($current_page - 1) .
                                                                    ($search ? '&search=' . urlencode($search) : '') .
                                                                    ($status ? '&status=' . $status : '')) ?>"
                                                        class="page-link">
                                                        <i class="mdi mdi-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : '' ?>">
                                                    <a href="<?php echo base_url('zbt_admin/home/users?page=' . $i .
                                                                    ($search ? '&search=' . urlencode($search) : '') .
                                                                    ($status ? '&status=' . $status : '')) ?>"
                                                        class="page-link"><?php echo $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($current_page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a href="<?php echo base_url('zbt_admin/users?page=' . ($current_page + 1) .
                                                                    ($search ? '&search=' . urlencode($search) : '') .
                                                                    ($status ? '&status=' . $status : '')) ?>"
                                                        class="page-link">
                                                        <i class="mdi mdi-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-5">
                                <div class="avatar-lg mx-auto mb-4">
                                    <div class="avatar-title bg-primary-subtle text-primary rounded-circle font-size-20">
                                        <i class="mdi mdi-account-group"></i>
                                    </div>
                                </div>
                                <h5>No Users Found</h5>
                                <p class="text-muted">There are no users matching your criteria.</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
