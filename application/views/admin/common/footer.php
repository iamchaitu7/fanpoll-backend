<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <script>
                    document.write(new Date().getFullYear())
                </script> © <?php echo config_item('application_name') ?>
            </div>
            <div class="col-sm-6">
                <div class="text-sm-end d-none d-sm-block">
                    Crafted with <i class="mdi mdi-heart text-danger"></i> by <a href="<?php echo config_item('author_link') ?>" target="_blank" class="text-reset"><?php echo config_item('author') ?></a>
                </div>
            </div>
        </div>
    </div>
</footer>
</div>
<!-- end main content-->

</div>
<!-- END layout-wrapper -->


<!-- Right bar overlay-->
<div class="rightbar-overlay"></div>

<!-- JAVASCRIPT -->
<script src="<?php echo base_url('admin_assets/') ?>libs/jquery/jquery.min.js"></script>
<script src="<?php echo base_url('admin_assets/') ?>libs/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo base_url('admin_assets/') ?>libs/metismenu/metisMenu.min.js"></script>
<script src="<?php echo base_url('admin_assets/') ?>libs/simplebar/simplebar.min.js"></script>
<script src="<?php echo base_url('admin_assets/') ?>libs/node-waves/waves.min.js"></script>
<script src="<?php echo base_url('admin_assets/') ?>libs/waypoints/lib/jquery.waypoints.min.js"></script>
<script src="<?php echo base_url('admin_assets/') ?>libs/jquery.counterup/jquery.counterup.min.js"></script>
<script src="<?php echo base_url('admin_assets/') ?>/libs/toastr/build/toastr.min.js"></script>
<script>
    toastr.options = {
        "closeButton": true,
        "debug": false,
        "newestOnTop": true,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": false,
        "onclick": null,
        "showDuration": 300,
        "hideDuration": 500,
        "timeOut": 1000,
        "extendedTimeOut": 1000,
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "slideDown",
        "hideMethod": "slideUp"
    }
</script>
<?php
if (isset($form_validation)) {
?>

    <script src="<?php echo base_url('admin_assets/') ?>libs/jquery/jquery-2.0.0.min.js"></script>
    <script src="<?php echo base_url('admin_assets/') ?>libs/metismenu/metisMenu.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/validation/formValidation.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/validation/bootstrap.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/inputmask/min/jquery.inputmask.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".input-mask").inputmask()
        });
    </script>
<?php
}

if (isset($datatable)) {
?>
    <!-- Required datatable js -->
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>
<?php
}
if (isset($datatable_buttons)) {
?>
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>
<?php
}
if (isset($sweet_alert)) {
?>
    <!-- Sweet Alerts js -->
    <script src="<?php echo base_url(); ?>admin_assets/libs/sweetalert2/sweetalert2.min.js"></script>
<?php
}

if (isset($apex_chart)) {
?>
    <!-- Sweet Alerts js -->
    <script src="<?php echo base_url(); ?>admin_assets/libs/apexcharts/apexcharts.min.js"></script>
<?php
}

if (isset($select_2)) {
?>
    <!-- Sweet Alerts js -->
    <script src="<?php echo base_url(); ?>admin_assets/libs/select2/js/select2.min.js"></script>
    <script>
        $(".select2").select2();
    </script>
<?php
}

if (isset($datepicker)) {
?>
    <script src="<?php echo base_url(); ?>admin_assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo base_url(); ?>admin_assets/libs/spectrum-colorpicker2/spectrum.min.js"></script>
<?php
}

if (isset($js_tree)) {
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jstree/3.2.1/jstree.min.js"></script>
<?php
}

if (isset($jq_ui)) {
?>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<?php
}
?>

<!-- App js -->
<script src="<?php echo base_url('admin_assets/') ?>js/app.js"></script>
<script>
    <?php
    if (config_item('console')) {
    ?>
        $('body').keydown(function(e) {
            if (e.which == 123) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 73) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 75) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 67) {
                e.preventDefault();
            }
            if (e.ctrlKey && e.shiftKey && e.which == 74) {
                e.preventDefault();
            }
        });
        ! function() {
            function detectDevTool(allow) {
                if (isNaN(+allow)) allow = 100;
                var start = +new Date();
                debugger;
                var end = +new Date();
                if (isNaN(start) || isNaN(end) || end - start > allow) {
                    console.log('DEVTOOLS detected ' + allow);
                }
            }
            if (window.attachEvent) {
                if (document.readyState === "complete" || document.readyState === "interactive") {
                    detectDevTool();
                    window.attachEvent('onresize', detectDevTool);
                    window.attachEvent('onmousemove', detectDevTool);
                    window.attachEvent('onfocus', detectDevTool);
                    window.attachEvent('onblur', detectDevTool);
                } else {
                    setTimeout(argument.callee, 0);
                }
            } else {
                window.addEventListener('load', detectDevTool);
                window.addEventListener('resize', detectDevTool);
                window.addEventListener('mousemove', detectDevTool);
                window.addEventListener('focus', detectDevTool);
                window.addEventListener('blur', detectDevTool);
            }
        }();
    <?php
    }
    ?>
</script>

<?php if ($this->session->flashdata('success')) { ?>
    <script>
        toastr.success("<?php echo $this->session->flashdata('success'); ?>");
    </script>
<?php } ?>

<?php if ($this->session->flashdata('error')) { ?>
    <script>
        toastr.error("<?php echo $this->session->flashdata('error'); ?>");
    </script>
<?php } ?>
</body>

</html>