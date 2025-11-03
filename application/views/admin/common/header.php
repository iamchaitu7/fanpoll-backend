<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="cache-control" content="max-age=604800" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo $title ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- App favicon -->
    <!-- <link rel="shortcut icon" href="<?php echo base_url('admin_assets/') ?>images/favicon.ico"> -->

    <link rel="stylesheet" type="text/css" href="<?php echo base_url('admin_assets/') ?>libs/toastr/build/toastr.min.css">
    <!-- place here plugins start -->
    <?php
    if (isset($form_validation)) {
    ?>
        <link rel="stylesheet" href="<?php echo base_url(); ?>admin_assets/validation/formValidation.css">
    <?php
    }

    if (isset($datatable)) {
    ?>
        <!-- DataTables -->
        <link href="<?php echo base_url(); ?>admin_assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo base_url(); ?>admin_assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <?php
    }
    if (isset($datatable_buttons)) {
    ?>
        <link href="<?php echo base_url(); ?>admin_assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <?php
    }
    if (isset($sweet_alert)) {
    ?>
        <!-- Sweet Alert-->
        <link href="<?php echo base_url(); ?>admin_assets/libs/sweetalert2/sweetalert2.min.css" rel="stylesheet" type="text/css" />
    <?php
    }

    if (isset($select_2)) {
    ?>
        <!-- Sweet Alert-->
        <link href="<?php echo base_url(); ?>admin_assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <?php
    }
    if (isset($datepicker)) {
    ?>
        <link href="<?php echo base_url(); ?>admin_assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">

        <link rel="stylesheet" href="<?php echo base_url(); ?>admin_assets/libs/@chenfengyuan/datepicker/datepicker.min.css">
    <?php
    }

    if (isset($js_tree)) {
    ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/themes/default/style.min.css" />
    <?php
    }

    if (isset($jq_ui)) {
    ?>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <?php
    }

    ?>
    <!-- place here plugins end -->
    <!-- Bootstrap Css -->
    <link href="<?php echo base_url('admin_assets/') ?>css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo base_url('admin_assets/') ?>css/icons.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo base_url('admin_assets/') ?>css/crcticons.css" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo base_url('admin_assets/') ?>css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />

    <script>
        var base_url = '<?php echo base_url() ?>';
    </script>
    <style>
        small.help-block {
            color: #f46a6a;
        }

        /* .gradient-1 {
            background: #E8CBC0;
            background: -webkit-linear-gradient(to right, #636FA4, #E8CBC0);
            background: linear-gradient(to right, #636FA4, #E8CBC0);
            border-color: #E8CBC0 !important;
        } */

        body.modal-open .page-content,
        body.modal-open #page-topbar,
        body.modal-open footer {
            -webkit-filter: blur(3px);
            -moz-filter: blur(3px);
            -o-filter: blur(3px);
            -ms-filter: blur(3px);
            filter: blur(3px);
        }

        body[data-topbar=dark] #page-topbar {
            background-color: #f5f6f8;
        }

        #page-topbar {
            -webkit-box-shadow: unset;
            box-shadow: unset;
        }

        body[data-topbar=dark] .header-item:hover {
            color: #3c4cfb;
        }

        body[data-topbar=dark] .header-item {
            color: #3c4cfb;
        }

        .header-profile-user {
            border: 1px solid #3c4cfb;
        }

        body[data-topbar=dark] .noti-icon i {
            color: #3c4cfb;
        }

        .topnav .navbar-nav .nav-item .nav-link.active {
            color: #3c4cfb;
        }

        .topnav .navbar-nav .nav-link:focus,
        .topnav .navbar-nav .nav-link:hover {
            color: #3c4cfb;
            background-color: transparent;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3c4cfb;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #3c4cfb;
            color: white;
        }

        .btn-primary {
            color: #fff;
            background-color: #3c4cfb;
            border-color: #3c4cfb;
        }

        .btn-primary:hover {
            color: #fff;
            background-color: #3c4cfb;
            border-color: #3c4cfb;
        }

        .btn-check:focus+.btn-primary,
        .btn-primary:focus {
            color: #fff;
            background-color: #3c4cfb;
            border-color: #3c4cfb;
            -webkit-box-shadow: 0 0 0 0.15rem rgba(60, 76, 251, .5);
            box-shadow: 0 0 0 0.15rem rgba(60, 76, 251, .5);
        }

        .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #3c4cfb;
            border-color: #3c4cfb;
        }

        .page-link:hover {
            z-index: 2;
            color: #3c4cfb;
            background-color: #faf7f8;
            border-color: #ced4da;
        }

        .btn-primary.disabled,
        .btn-primary:disabled {
            color: #fff;
            background-color: #3c4cfb;
            border-color: #3c4cfb;
        }

        .select2-container--default .select2-results__option[aria-selected=true]:hover {
            background-color: #3c4cfb;
            color: #fff;
        }

        .page-link:focus {
            -webkit-box-shadow: 0 0 0 0.15rem rgba(60, 76, 251, .25);
            box-shadow: 0 0 0 0.15rem rgba(60, 76, 251, .25);
        }

        .text-theme {
            color: #3c4cfb;
        }
    </style>

    <?php
    $header_class = null;
    if (isset($hide_top_bar)) {
        $header_class = 'd-none';

    ?>
        <style>
            body[data-layout=horizontal] .page-content {
                margin-top: 10px;
                padding: calc(10px + 1.25rem) calc(0.55rem / 2) 60px calc(0.55rem / 2);
            }
        </style>
    <?php
    }
    ?>
</head>


<body data-layout="horizontal" data-topbar="dark">
    <!-- Begin page -->
    <div id="layout-wrapper">


        <header class="<?php echo $header_class ?>" id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="<?php echo base_url('zbt_admin') ?>" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="<?php echo base_url('admin_assets/') ?>images/logo-sm.png" alt="" height="50">
                            </span>
                            <span class="logo-lg">
                                <img src="<?php echo base_url('admin_assets/') ?>images/logo-dark.png" alt="" height="50">
                            </span>
                        </a>

                        <a href="<?php echo base_url('zbt_admin') ?>" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="<?php echo base_url('admin_assets/') ?>images/logo-sm.png" alt="" height="50" class="rounded-3">
                            </span>
                            <span class="logo-lg">
                                <img src="<?php echo base_url('admin_assets/images/logo-light.png') ?>" alt="" height="50" class="rounded-3">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 d-lg-none header-item waves-effect waves-light" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                </div>

                <div class="d-flex">


                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                            <i class="uil-minus-path"></i>
                        </button>
                    </div>




                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="https://ui-avatars.com/api/?bold=true&background=3c4cfb&name=<?php echo get_admindetails()->full_name ?>&rounded=true&color=fff" alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1 fw-medium font-size-15"><?php echo get_admindetails()->full_name ?></span>
                            <i class="uil-angle-down d-none d-xl-inline-block font-size-15"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <!-- <a class="dropdown-item" href="<?php echo base_url('zbt_admin/settings') ?>"><i class="uil uil-wrench font-size-18 align-middle text-muted me-1"></i> <span class="align-middle">Settings</span></a> -->
                            <a class="dropdown-item" href="<?php echo base_url('zbt_admin/common/logout') ?>"><i class="uil uil-sign-out-alt font-size-18 align-middle me-1 text-muted"></i> <span class="align-middle">Sign out</span></a>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            $this->load->view('admin/common/sidebar_hor');
            ?>
        </header>
        <div class="main-content">