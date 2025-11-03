<div class="page-content">
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Poll Dashboard</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Home</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Stats Cards Row -->
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="card border border-primary gradient-1">
                    <div class="card-body">
                        <div class="float-end mt-2">
                            <div class="just_for_dummy"></div>
                        </div>
                        <div>
                            <h4 class="mb-1 mt-1 text-primary"><span data-plugin="counterup1"><?php echo $total_users ?></span></h4>
                            <p class="text-mute text-primary mb-0">Total Users</p>
                        </div>
                        <p class="text-mute mt-3 mb-0">
                            <span class="text-<?php echo ($users_growth >= 0) ? 'success' : 'danger'; ?> me-2">
                                <i class="mdi mdi-arrow-<?php echo ($users_growth >= 0) ? 'up' : 'down'; ?>-bold me-1"></i><?php echo $users_growth ?>%
                            </span> from last month
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card border border-success gradient-2">
                    <div class="card-body">
                        <div class="float-end mt-2">
                            <div class="just_for_dummy_green"></div>
                        </div>
                        <div>
                            <h4 class="mb-1 mt-1 text-success"><span data-plugin="counterup1"><?php echo $total_polls ?></span></h4>
                            <p class="text-mute text-success mb-0">Total Polls</p>
                        </div>
                        <p class="text-mute mt-3 mb-0">
                            <span class="text-<?php echo ($polls_growth >= 0) ? 'success' : 'danger'; ?> me-2">
                                <i class="mdi mdi-arrow-<?php echo ($polls_growth >= 0) ? 'up' : 'down'; ?>-bold me-1"></i><?php echo $polls_growth ?>%
                            </span> from last month
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card border border-info gradient-3">
                    <div class="card-body">
                        <div class="float-end mt-2">
                            <div class="just_for_dummy_info"></div>
                        </div>
                        <div>
                            <h4 class="mb-1 mt-1 text-info"><span data-plugin="counterup1"><?php echo $total_votes ?></span></h4>
                            <p class="text-mute text-info mb-0">Total Votes</p>
                        </div>
                        <p class="text-mute mt-3 mb-0">
                            <span class="text-<?php echo ($votes_growth >= 0) ? 'success' : 'danger'; ?> me-2">
                                <i class="mdi mdi-arrow-<?php echo ($votes_growth >= 0) ? 'up' : 'down'; ?>-bold me-1"></i><?php echo $votes_growth ?>%
                            </span> from last month
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-xl-3">
                <div class="card border border-warning gradient-4">
                    <div class="card-body">
                        <div class="float-end mt-2">
                            <div class="just_for_dummy_warning"></div>
                        </div>
                        <div>
                            <h4 class="mb-1 mt-1 text-warning"><span data-plugin="counterup1"><?php echo $active_polls ?></span></h4>
                            <p class="text-mute text-warning mb-0">Active Polls</p>
                        </div>
                        <p class="text-mute mt-3 mb-0">
                            <span class="text-<?php echo ($active_polls_change >= 0) ? 'success' : 'danger'; ?> me-2">
                                <i class="mdi mdi-arrow-<?php echo ($active_polls_change >= 0) ? 'up' : 'down'; ?>-bold me-1"></i><?php echo $active_polls_change ?>%
                            </span> from yesterday
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Poll Analytics (<?php echo date('Y') ?>)</h4>

                        <div class="mt-1">
                            <ul class="list-inline main-chart mb-0">
                                <li class="list-inline-item chart-border-left me-0">
                                    <h3 class="text-primary"><span data-plugin="counterup1"><?php echo $total_polls ?></span>
                                        <span class="text-muted d-inline-block font-size-15 ms-3">Total Polls</span>
                                    </h3>
                                </li>
                                <li class="list-inline-item chart-border-left me-0">
                                    <h3 class="text-success"><span data-plugin="counterup1"><?php echo $total_votes ?></span>
                                        <span class="text-muted d-inline-block font-size-15 ms-3">Total Votes</span>
                                    </h3>
                                </li>
                                <li class="list-inline-item chart-border-left me-0">
                                    <h3 class="text-info"><span data-plugin="counterup1"><?php echo $total_comments ?></span>
                                        <span class="text-muted d-inline-block font-size-15 ms-3">Total Comments</span>
                                    </h3>
                                </li>
                            </ul>
                        </div>

                        <div class="mt-3">
                            <div id="poll-analytics-chart" class="apex-charts" dir="ltr"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">User Activity</h4>
                        <div id="user-activity-chart" class="apex-charts" dir="ltr"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Row -->
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <h4 class="card-title mb-0">Recent Polls</h4>
                            <div class="ms-auto">
                                <a href="<?php echo base_url('zbt_admin/polls') ?>" class="btn btn-primary btn-sm">View All</a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-nowrap align-middle mb-0">
                                <tbody>
                                    <?php foreach ($recent_polls as $poll): ?>
                                        <tr>
                                            <td style="width: 50px;">
                                                <div class="avatar-sm">
                                                    <span class="avatar-title rounded-circle bg-primary-subtle text-white font-size-12 fw-bold">
                                                        <?php echo substr($poll->title, 0, 2) ?>
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <h5 class="font-size-14 mb-1"><?php echo character_limiter($poll->title, 40) ?></h5>
                                                <p class="text-muted mb-0">by <?php echo $poll->creator_name ?></p>
                                            </td>
                                            <td>
                                                <div class="text-end">
                                                    <h5 class="font-size-14 mb-0"><?php echo $poll->total_votes ?> votes</h5>
                                                    <p class="text-muted mb-0"><?php echo time_elapsed_string($poll->created_at) ?></p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-4">
                            <h4 class="card-title mb-0">Top Users</h4>
                            <div class="ms-auto">
                                <a href="<?php echo base_url('zbt_admin/home/users') ?>" class="btn btn-primary btn-sm">View All</a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-nowrap align-middle mb-0">
                                <tbody>
                                    <?php foreach ($top_users as $user): ?>
                                        <tr>
                                            <td style="width: 50px;">
                                                <img src="<?php echo base_url('uploads/profile_pictures/' . $user->profile_picture) ?>"
                                                    alt="" class="avatar-sm rounded-circle">
                                            </td>
                                            <td>
                                                <h5 class="font-size-14 mb-1"><?php echo $user->full_name ?></h5>
                                                <p class="text-muted mb-0"><?php echo $user->email ?></p>
                                            </td>
                                            <td>
                                                <div class="text-end">
                                                    <h5 class="font-size-14 mb-0"><?php echo $user->polls_created ?> polls</h5>
                                                    <p class="text-muted mb-0"><?php echo $user->total_votes ?> votes</p>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> <!-- container-fluid -->
</div>
<!-- End Page-content -->