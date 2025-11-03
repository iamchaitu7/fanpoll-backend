<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Manage Polls</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('zbt_admin/') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item active">Polls</li>
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
                        <form method="GET" action="<?php echo base_url('zbt_admin/polls') ?>">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Search Polls</label>
                                    <input type="text" class="form-control" name="search"
                                        value="<?php echo htmlspecialchars($search ?? '') ?>"
                                        placeholder="Search by title, description, or creator...">
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
                                        <a href="<?php echo base_url('zbt_admin/polls') ?>" class="btn btn-secondary">
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

        <!-- Polls Table -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">
                                Polls List
                                <span class="badge bg-primary ms-2"><?php echo $total_polls ?> Total</span>
                            </h4>
                        </div>

                        <?php if (!empty($polls)): ?>
                            <div class="table-responsive">
                                <table class="table table-nowrap table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Poll Details</th>
                                            <th>Creator</th>
                                            <th>Stats</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Expires</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($polls as $poll): ?>
                                            <tr>
                                                <td>
                                                    <strong>#<?php echo $poll->id ?></strong>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($poll->image_path): ?>
                                                            <div class="avatar-sm me-3">
                                                                <img src="<?php echo base_url('uploads/poll_images/' . $poll->image_path) ?>"
                                                                    alt="" class="img-fluid rounded">
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h5 class="font-size-14 mb-1">
                                                                <?php echo character_limiter($poll->title, 50) ?>
                                                            </h5>
                                                            <?php if ($poll->description): ?>
                                                                <p class="text-muted mb-0 font-size-12">
                                                                    <?php echo character_limiter($poll->description, 80) ?>
                                                                </p>
                                                            <?php endif; ?>
                                                            <?php if ($poll->hashtags): ?>
                                                                <div class="mt-1">
                                                                    <?php
                                                                    $hashtags = explode(',', $poll->hashtags);
                                                                    foreach ($hashtags as $tag): ?>
                                                                        <span class="badge bg-info font-size-10 me-1">#<?php echo trim($tag) ?></span>
                                                                    <?php endforeach; ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $poll->creator_name ?></h6>
                                                        <p class="text-muted mb-0 font-size-12"><?php echo $poll->creator_email ?></p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="text-center">
                                                        <h6 class="mb-1"><?php echo $poll->total_votes ?? 0 ?></h6>
                                                        <p class="text-muted mb-0 font-size-12">Votes</p>
                                                        <h6 class="mb-1 mt-2"><?php echo $poll->likes_count ?? 0 ?></h6>
                                                        <p class="text-muted mb-0 font-size-12">Likes</p>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    $status_text = ucfirst($poll->status);

                                                    switch ($poll->status) {
                                                        case 'active':
                                                            $is_expired = strtotime($poll->expires_at) <= time();
                                                            if ($is_expired) {
                                                                $status_class = 'bg-warning';
                                                                $status_text = 'Expired';
                                                            } else {
                                                                $status_class = 'bg-success';
                                                            }
                                                            break;
                                                        case 'inactive':
                                                            $status_class = 'bg-secondary';
                                                            break;
                                                        case 'deleted':
                                                            $status_class = 'bg-danger';
                                                            break;
                                                        default:
                                                            $status_class = 'bg-light text-dark';
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class ?> font-size-12">
                                                        <?php echo $status_text ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="font-size-12">
                                                        <?php echo date('M j, Y', strtotime($poll->created_at)) ?><br>
                                                        <span class="text-muted"><?php echo date('g:i A', strtotime($poll->created_at)) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="font-size-12">
                                                        <?php echo date('M j, Y', strtotime($poll->expires_at)) ?><br>
                                                        <span class="text-muted"><?php echo date('g:i A', strtotime($poll->expires_at)) ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="dropdown">
                                                        <button class="btn btn-light dropdown-toggle" type="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="mdi mdi-dots-horizontal"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item" href="<?php echo base_url('zbt_admin/polls/view/' . $poll->id) ?>">
                                                                    <i class="mdi mdi-eye"></i> View Details
                                                                </a>
                                                            </li>
                                                            <?php
                                                            if ($poll->status != 'deleted') { ?>
                                                                <li>
                                                                    <button class="dropdown-item text-danger"
                                                                        onclick="deletePoll(<?php echo $poll->id ?>)">
                                                                        <i class="mdi mdi-delete"></i> Delete Poll</a>
                                                                    </button>
                                                                </li>
                                                            <?php
                                                            }
                                                            ?>
                                                        </ul>
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
                                                    <a href="<?php echo base_url('zbt_admin/polls?page=' . ($current_page - 1) .
                                                                    ($search ? '&search=' . urlencode($search) : '') .
                                                                    ($status ? '&status=' . $status : '')) ?>"
                                                        class="page-link">
                                                        <i class="mdi mdi-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>

                                            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                                <li class="page-item <?php echo ($i == $current_page) ? 'active' : '' ?>">
                                                    <a href="<?php echo base_url('zbt_admin/polls?page=' . $i .
                                                                    ($search ? '&search=' . urlencode($search) : '') .
                                                                    ($status ? '&status=' . $status : '')) ?>"
                                                        class="page-link"><?php echo $i ?></a>
                                                </li>
                                            <?php endfor; ?>

                                            <?php if ($current_page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a href="<?php echo base_url('zbt_admin/polls?page=' . ($current_page + 1) .
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
                                        <i class="mdi mdi-poll"></i>
                                    </div>
                                </div>
                                <h5>No Polls Found</h5>
                                <p class="text-muted">There are no polls matching your criteria.</p>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>