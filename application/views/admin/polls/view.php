<div class="page-content">
    <div class="container-fluid">

        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Poll Details</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="<?php echo base_url('zbt_admin/') ?>">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo base_url('zbt_admin/polls') ?>">Polls</a></li>
                            <li class="breadcrumb-item active">Poll #<?php echo $poll->id ?></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Poll Header -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-3">
                                    <?php if ($poll->image_path): ?>
                                        <div class="avatar-lg me-3 align-content-around">
                                            <a href="<?php echo base_url('uploads/poll_images/' . $poll->image_path) ?>" target="_blank">
                                                <img src="<?php echo base_url('uploads/poll_images/' . $poll->image_path) ?>"
                                                    alt="" class="img-fluid rounded mh-100">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="mb-1"><?php echo $poll->title ?></h4>
                                        <p class="text-muted mb-0">by <?php echo $poll->creator_name ?></p>
                                    </div>
                                </div>

                                <?php if ($poll->description): ?>
                                    <p class="mb-3"><?php echo $poll->description ?></p>
                                <?php endif; ?>

                                <?php if ($poll->url): ?>
                                    <p class="mb-2">
                                        <i class="mdi mdi-link me-1"></i>
                                        <a href="<?php echo $poll->url ?>" target="_blank"><?php echo $poll->url ?></a>
                                    </p>
                                <?php endif; ?>

                                <?php if ($poll->hashtags): ?>
                                    <div class="mb-3">
                                        <?php
                                        $hashtags = explode(',', $poll->hashtags);
                                        foreach ($hashtags as $tag): ?>
                                            <span class="badge bg-info me-1">#<?php echo trim($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Poll Stats -->
        <div class="row">
            <div class="col-md-3">
                <div class="card border border-primary">
                    <div class="card-body text-center">
                        <h3 class="text-primary mb-1"><?php echo $poll->total_votes ?? 0 ?></h3>
                        <p class="text-muted mb-0">Total Votes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border border-success">
                    <div class="card-body text-center">
                        <h3 class="text-success mb-1"><?php echo $engagement['unique_voters'] ?></h3>
                        <p class="text-muted mb-0">Unique Voters</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border border-info">
                    <div class="card-body text-center">
                        <h3 class="text-info mb-1"><?php echo $poll->comments_count ?? 0 ?></h3>
                        <p class="text-muted mb-0">Comments</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border border-danger">
                    <div class="card-body text-center">
                        <h3 class="text-danger mb-1"><?php echo $poll->likes_count ?? 0 ?></h3>
                        <p class="text-muted mb-0">Likes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card border border-warning">
                    <div class="card-body text-center">
                        <h3 class="text-warning mb-1"><?php echo $engagement['engagement_rate'] ?>%</h3>
                        <p class="text-muted mb-0">Engagement Rate</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Poll Options Results -->
        <div class="row">
            <div class="col-xl-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Poll Results</h4>

                        <?php if (!empty($options)): ?>
                            <div class="poll-options">
                                <?php
                                $total_votes = $poll->total_votes ?? 0;
                                foreach ($options as $option):
                                    $percentage = $total_votes > 0 ? round(($option->vote_count / $total_votes) * 100, 1) : 0;
                                ?>
                                    <div class="option-result mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0"><?php echo $option->option_text ?></h6>
                                            <span class="badge bg-primary"><?php echo $option->vote_count ?> votes (<?php echo $percentage ?>%)</span>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar" role="progressbar"
                                                style="width: <?php echo $percentage ?>%"
                                                aria-valuenow="<?php echo $percentage ?>"
                                                aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No options found for this poll.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Poll Information</h4>

                        <div class="table-responsive">
                            <table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <td>Poll ID:</td>
                                        <td><strong>#<?php echo $poll->id ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td>Status:</td>
                                        <td>
                                            <?php
                                            $status_class = '';
                                            $is_expired = strtotime($poll->expires_at) <= time();
                                            if ($poll->status === 'active' && $is_expired) {
                                                $status_class = 'bg-warning';
                                                $status_text = 'Expired';
                                            } else {
                                                switch ($poll->status) {
                                                    case 'active':
                                                        $status_class = 'bg-success';
                                                        $status_text = 'Active';
                                                        break;
                                                    case 'inactive':
                                                        $status_class = 'bg-secondary';
                                                        $status_text = 'Inactive';
                                                        break;
                                                    default:
                                                        $status_class = 'bg-light text-dark';
                                                        $status_text = ucfirst($poll->status);
                                                }
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class ?>"><?php echo $status_text ?></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Created:</td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($poll->created_at)) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Expires:</td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($poll->expires_at)) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Creator:</td>
                                        <td>
                                            <?php echo $poll->creator_name ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Recent Comments -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Recent Comments</h4>
                        </div>

                        <?php if (!empty($comments)): ?>
                            <div class="comments-list">
                                <?php foreach (array_slice($comments, 0, 10) as $comment): ?>
                                    <div class="d-flex mb-3">
                                        <div class="avatar-sm me-3">
                                            <img src="<?php echo base_url('uploads/profile_pictures/' . ($comment->profile_picture ?: 'default.png')) ?>"
                                                alt="" class="img-fluid rounded-circle">
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1"><?php echo $comment->full_name ?></h6>
                                            <p class="mb-1"><?php echo $comment->comment ?></p>
                                            <small class="text-muted"><?php echo time_elapsed_string($comment->created_at) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <div class="avatar-lg mx-auto mb-3">
                                    <div class="avatar-title bg-primary-subtle text-white rounded-circle font-size-20">
                                        <i class="mdi mdi-comment-outline"></i>
                                    </div>
                                </div>
                                <h5>No Comments Yet</h5>
                                <p class="text-muted">This poll hasn't received any comments yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function deletePoll(pollId) {
        if (confirm('Are you sure you want to delete this poll? This action cannot be undone.')) {
            $.ajax({
                url: '<?php echo base_url("admin/dashboard/delete_poll/") ?>' + pollId,
                method: 'POST',
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200) {
                        alert('Poll deleted successfully!');
                        window.location.href = '<?php echo base_url("admin/polls") ?>';
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the poll.');
                }
            });
        }
    }
</script>