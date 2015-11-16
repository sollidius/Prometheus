<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="index.php?page=dashboard">Prometheus</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse">
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="fa fa-user fa-fw"></i>  <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-user">
            <li><a href="index.php?page=usettings"><i class="fa fa-gear fa-fw"></i> Settings</a>
            </li>
            <li class="divider"></li>
            <li><a href="index.php?page=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a>
            </li>
        </ul>
        <!-- /.dropdown-user -->
  </li>
      </ul>
    </div>
  </div>
</nav>

<div class="container-fluid">
    <div class="row">
      <div class="col-sm-3 col-md-2 sidebar">
        <ul class="nav nav-sidebar">
          <li>
              <a href="index.php?page=dashboard"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
          </li>
          <?php if  ($db_rank == 1) { echo '
          <li>
              <a href="index.php?page=settings"><i class="fa fa-table fa-fw"></i> Einstellungen</a>
          </li>
           '; } ?>
          <?php if  ($db_rank == 1) { echo '
          <li>
              <a href="index.php?page=users"><i class="fa fa-table fa-fw"></i> Benutzer</a>
          </li>
           '; } ?>
           <?php if  ($db_rank == 1) { echo '
          <li>
              <a href="index.php?page=rootserver"><i class="fa fa-edit fa-fw"></i> Rootserver</a>
          </li>
           '; } ?>
           <?php if  ($db_rank == 1) { echo '
          <li>
              <a href="index.php?page=templates"><i class="fa fa-edit fa-fw"></i> Vorlagen</a>
          </li>
           '; } ?>
          <li>
              <a href="index.php?page=gameserver"><i class="fa fa-edit fa-fw"></i> Gameserver</a>
          </li>
        </ul>
      </div>
      <div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
