<script>
    function togglePollStatus(pollId, status) {
        let confirmMessage = '';
        switch (status) {
            case 'active':
                confirmMessage = 'Are you sure you want to activate this poll?';
                break;
            case 'inactive':
                confirmMessage = 'Are you sure you want to deactivate this poll?';
                break;
            case 'deleted':
                confirmMessage = 'Are you sure you want to delete this poll? This action cannot be undone.';
                break;
            default:
                confirmMessage = 'Are you sure you want to change the status of this poll?';
        }

        if (confirm(confirmMessage)) {
            $.ajax({
                url: '<?php echo base_url("zbt_admin/home/toggle_poll_status") ?>',
                method: 'POST',
                data: {
                    poll_id: pollId,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 200) {
                        // Show success message
                        alert('Poll status updated successfully!');
                        // Reload the page to reflect changes
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating the poll status.');
                }
            });
        }
    }


    function deletePoll(pollId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo base_url("zbt_admin/polls/delete_poll") ?>',
                    method: 'POST',
                    data: { poll_id: pollId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 200) {
                            Swal.fire(
                                'Deleted!',
                                'Your poll has been deleted.',
                                'success'
                            );
                            // Reload the page to reflect changes
                            location.reload();
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message,
                                'error'
                            );
                        }
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'An error occurred while deleting the poll.',
                            'error'
                        );
                    }
                });
            }
        });
    }
</script>