<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="<?php echo base_url('dashboard') ?>" class="logo logo-dark">
            <span class="logo-sm">
                <img src="<?php echo base_url('assets/') ?>images/logo-sm.png" alt="" height="50">
            </span>
            <span class="logo-lg">
                <img src="<?php echo base_url('assets/') ?>images/logo-dark.png" alt="" height="50">
            </span>
        </a>

        <a href="<?php echo base_url('dashboard') ?>" class="logo logo-light">
            <span class="logo-sm">
                <img src="<?php echo base_url('assets/') ?>images/logo-sm.png" alt="" height="50">
            </span>
            <span class="logo-lg">
                <img src="<?php echo base_url('assets/') ?>images/logo-light.png" alt="" height="50">
            </span>
        </a>
    </div>

    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect vertical-menu-btn">
        <i class="fa fa-fw fa-bars"></i>
    </button>

    <div data-simplebar class="sidebar-menu-scroll">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">Menu</li>

                <li>
                    <a href="<?php echo base_url('dashboard') ?>">
                        <i class="uil-home-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo base_url('dashboard/categorys') ?>">
                        <i class="uil-cloud-database-tree"></i>
                        <span>Categorys</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url('dashboard/products/hsncodes') ?>">
                        <i class="uil-lightbulb"></i>
                        <span>HSN Codes</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo base_url('dashboard/products') ?>">
                        <i class="uil-shopping-basket"></i>
                        <span>Products</span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="uil-server"></i>
                        <span>Stock</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li><a href="<?php echo base_url('dashboard/stock/crate') ?>">Create Stock</a></li>
                        <li><a href="<?php echo base_url('dashboard/stock/stock_report') ?>">Stock</a></li>
                    </ul>
                </li>
                <li>
                    <a href="<?php echo base_url('dashboard/pos/') ?>">
                        <i class="uil-money-insert"></i>
                        <span>POS</span>
                    </a>
                </li>

                <li>
                    <a href="<?php echo base_url('dashboard/settings') ?>">
                        <i class="uil-wrench"></i>
                        <span>Settings</span>
                    </a>
                </li>
                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                        <i class="uil-bill"></i>
                        <span>Reports</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="true">
                        <li><a href="<?php echo base_url('dashboard/reports/sales_report') ?>">Sales Report</a></li>
                        <li><a href="<?php echo base_url('dashboard/reports/customers') ?>">Customer</a></li>
                    </ul>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->



<!-- ============================================================== -->
<!-- Start right Content here -->
<!-- ============================================================== -->
<div class="main-content">