@section('side')
<div class="col-md-3 left_col menu_fixed">
  <div class="left_col scroll-view">
  <div class="navbar nav_title" style="border: 0;">
    <a href="index.html" class="site_title"><span>Template Creator!!</span></a>
  </div>

    <div class="clearfix"></div>

    <br />

    <!-- sidebar menu -->
    <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
      <div class="menu_section">
        <h3>Menu</h3>
        <ul class="nav side-menu">
          <li><a href="#"><i class="fa fa-home"></i> Home</a></li>
          <!-- <li><a href="#"><i class="fa fa-dashboard"></i> 工数管理</a></li>
          <li><a href="#"><i class="fa fa-tasks"></i> 案件管理</a></li> -->
        </ul>
      </div>

    </div>
    <!-- /sidebar menu -->

    <!-- /menu footer buttons -->
    <div class="sidebar-footer hidden-small">
      <a data-toggle="tooltip" data-placement="top" title="Settings">
        <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>
      </a>
      <a data-toggle="tooltip" data-placement="top" title="FullScreen">
        <span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>
      </a>
      <a data-toggle="tooltip" data-placement="top" title="Lock">
        <span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>
      </a>
      <a data-toggle="tooltip" data-placement="top" title="Logout" href="login.html">
        <span class="glyphicon glyphicon-off" aria-hidden="true"></span>
      </a>
    </div>
    <!-- /menu footer buttons -->
  </div>
</div>

@endsection

@push('scripts')
<script>
    $(function () {
    })

</script>
@endpush
