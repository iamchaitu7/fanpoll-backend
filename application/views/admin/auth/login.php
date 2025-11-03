<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title> <?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Creative Central" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url('admin_assets/') ?>images/favicon.ico">

    <link rel="stylesheet" type="text/css" href="<?php echo base_url('admin_assets/') ?>libs/toastr/build/toastr.min.css">
    <!-- Bootstrap Css -->
    <link href="<?php echo base_url('admin_assets/') ?>css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo base_url('admin_assets/') ?>css/icons.min.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo base_url('admin_assets/') ?>css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <link rel="stylesheet" href="<?php echo base_url(); ?>admin_assets/validation/formValidation.css">

    <style>
        small.help-block {
            color: #f46a6a;
        }
    </style>
</head>

<body class="authentication-bg">
    <div class="account-pages my-5 pt-sm-5">
        <div class="container">
            
            <div class="row align-items-center justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-5">
                    <div class="card">

                        <div class="card-body p-4">

                            <div class="p-2 mt-4">
                                <div class="user-thumb text-center mb-4">
                                    <img src="<?php echo base_url('admin_assets/images/logo-sm.png') ?>" class="rounded-circle img-thumbnail avatar-lg" alt="Creative Central">
                                </div>
                                <div class="text-center mt-2">
                                    <h5 class="text-primary">Welcome Back !</h5>
                                    <p class="text-muted">Sign in to continue to <?php echo config_item('application_name') ?>.</p>
                                </div>
                                <form action="<?php echo base_url('zbt_admin/common/check_login') ?>" id="login_form" name="login_form">


                                    <div class="form-group mb-3 ">
                                        <label class="form-label" for="username">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username">
                                    </div>

                                    <div class="form-group mb-3 ">

                                        <label class="form-label" for="userpassword">Password</label>
                                        <input type="password" class="form-control" name="userpassword" id="userpassword" placeholder="Enter password">
                                    </div>

                                    <div class="mt-3 text-end">
                                        <button class="btn btn-primary w-sm waves-effect waves-light" type="submit" id="submit_button">Log In</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                    <div class="mt-5 text-center">
                        <p>© <script>
                                document.write(new Date().getFullYear())
                            </script> <?php echo config_item('application_name') ?>. Crafted with <i class="mdi mdi-heart text-danger"></i> by <?php echo config_item('author') ?></p>
                    </div>

                </div>
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>

    <!-- JAVASCRIPT -->
    <script src="<?php echo base_url('admin_assets/') ?>libs/jquery/jquery.min.js"></script>
    <!-- <script src="<?php echo base_url('admin_assets/') ?>libs/jquery/jquery-2.0.0.min.js"></script> -->
    <script src="<?php echo base_url('admin_assets/') ?>libs/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo base_url('admin_assets/') ?>libs/metismenu/metisMenu.min.js"></script>
    <script src="<?php echo base_url('admin_assets/') ?>libs/simplebar/simplebar.min.js"></script>
    <script src="<?php echo base_url('admin_assets/') ?>libs/node-waves/waves.min.js"></script>
    <script src="<?php echo base_url('admin_assets/') ?>libs/waypoints/lib/jquery.waypoints.min.js"></script>
    <script src="<?php echo base_url('admin_assets/') ?>libs/jquery.counterup/jquery.counterup.min.js"></script>

    <script src="<?php echo base_url('admin_assets/') ?>/libs/toastr/build/toastr.min.js"></script>

    <script src="<?php echo base_url(); ?>admin_assets/validation/formValidation.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/validation/bootstrap.js"></script>
    <!-- App js -->
    <script src="<?php echo base_url('admin_assets/') ?>js/app.js"></script>

    <script>
        toastr.options = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": 300,
            "hideDuration": 1000,
            "timeOut": 5000,
            "extendedTimeOut": 1000,
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>

    <script>
        $(function() {

            // Login
            $('#login_form').formValidation({
                message: 'This value is not valid',
                icon: {
                    validating: 'glyphicon glyphicon-refresh'
                },
                fields: {
                    username: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter Username or email'
                            }
                        }
                    },
                    userpassword: {
                        validators: {
                            notEmpty: {
                                message: 'Please enter password'
                            }
                        }
                    }
                }
            }).on('success.form.fv', function(e) {
                // Prevent form submission
                e.preventDefault();

                // Get the form instance
                var $form = $(e.target);

                // Get the FormValidation instance
                var bv = $form.data('formValidation');

                // Use Ajax to submit form data
                $.ajax({
                    url: $form.attr('action'),
                    type: "POST",
                    data: new FormData(this), // Data sent to server, a set of key/value pairs (i.e. form fields and values)
                    contentType: false, // The content type used when sending data to the server.
                    cache: false, // To unable request pages to be cached
                    processData: false, // To send DOMDocument or non processed data file it is set to false
                    success: function(result) {
                        var obj = JSON.parse(result);

                        if (obj.status == 200) {

                            toastr["success"]("message", obj.message);

                            setTimeout(function() {
                                window.location.reload();
                            }, 3000);
                        } else {
                            toastr["error"]("message", obj.message);
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>