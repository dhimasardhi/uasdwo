<?php
$dbHost = "localhost";
$dbDatabase = "whsakila2021";
$dbUser = "root";
$dbPasswrod = "";

$mysqli = mysqli_connect($dbHost, $dbUser, $dbPasswrod, $dbDatabase);

//QUERY CHART PERTAMA

//query untuk tahu SUM(Amount) semuanya
$sql = "SELECT sum(amount) as tot from fakta_pendapatan";
$tot = mysqli_query($mysqli, $sql);
$tot_amount = mysqli_fetch_row($tot);

//echo $tot_amount[0];

//query untuk ambil penjualan berdasarkan kategori, query sudah dimodifikasi
//ditambahkan label variabel DATA. (teknik gak jelas :D)

$sql = "SELECT concat('name:',f.kategori) as name, concat('y:', sum(fp.amount)*100/" . $tot_amount[0] . ") as y, concat('drilldown:', f.kategori) as drilldown
            FROM film f
            JOIN fakta_pendapatan fp ON (f.film_id = fp.film_id)
            GROUP BY name
            ORDER BY y DESC";
//echo $sql;
$all_kat = mysqli_query($mysqli, $sql);

while ($row = mysqli_fetch_all($all_kat)) {
  $data[] = $row;
}


$json_all_kat = json_encode($data);

//CHART KEDUA (DRILL DOWN)

//query untuk tahu SUM(Amount) semua kategori
$sql = "SELECT f.kategori kategori, sum(fp.amount) as tot_kat
            FROM fakta_pendapatan fp
            JOIN film f ON (f.film_id = fp.film_id)
            GROUP BY kategori";
$hasil_kat = mysqli_query($mysqli, $sql);

while ($row = mysqli_fetch_all($hasil_kat)) {
  $tot_all_kat[] = $row;
}

//print_r($tot_all_kat);
//function untuk nyari total_per_kat 

//echo count($tot_per_kat[0]);
//echo $tot_per_kat[0][0][1];

function cari_tot_kat($kat_dicari, $tot_all_kat)
{
  $counter = 0;
  // echo $tot_all_kat[0];
  while ($counter < count($tot_all_kat[0])) {
    if ($kat_dicari == $tot_all_kat[0][$counter][0]) {
      $tot_kat = $tot_all_kat[0][$counter][1];
      return $tot_kat;
    }
    $counter++;
  }
}

//query untuk ambil penjualan di kategori berdasarkan bulan (CLEAN)
$sql = "SELECT f.kategori kategori, 
            t.bulan as bulan, 
            sum(fp.amount) as pendapatan_kat
            FROM film f
            JOIN fakta_pendapatan fp ON (f.film_id = fp.film_id)
            JOIN time t ON (t.time_id = fp.time_id)
            GROUP BY kategori, bulan";
$det_kat = mysqli_query($mysqli, $sql);
$i = 0;
while ($row = mysqli_fetch_all($det_kat)) {
  //echo $row;
  $data_det[] = $row;
}

//print_r($data_det);

//PERSIAPAN DATA DRILL DOWN - TEKNIK CLEAN  
$i = 0;

//inisiasi string DATA
$string_data = "";
$string_data .= '{name:"' . $data_det[0][$i][0] . '", id:"' . $data_det[0][$i][0] . '", data: [';


// echo cari_tot_kat("Action", $tot_all_kat);
foreach ($data_det[0] as $a) {
  //echo cari_tot_kat($a[0], $tot_all_kat);

  if ($i < count($data_det[0]) - 1) {
    if ($a[0] != $data_det[0][$i + 1][0]) {
      $string_data .= '["' . $a[1] . '", ' .
        $a[2] * 100 / cari_tot_kat($a[0], $tot_all_kat) . ']]},';
      $string_data .= '{name:"' . $a[0] . '", id:"' . $a[0]    . '", data: [';
    } else {
      $string_data .= '["' . $a[1] . '", ' .
        $a[2] * 100 / cari_tot_kat($a[0], $tot_all_kat) . '], ';
    }
  } else {

    $string_data .= '["' . $a[1] . '", ' .
      $a[2] * 100 / cari_tot_kat($a[0], $tot_all_kat) . ']]}';
  }


  $i = $i + 1;
}

//PERSIAPAN DASHBOARD ATAS (KOTAK)
//1. Total Customer
$sql2 = "SELECT count(distinct nama) as jml_cust from customer";
$jml_c = mysqli_query($mysqli, $sql2);
$jml_cust = mysqli_fetch_assoc($jml_c);

//2. Total Sales
$sql3 = "SELECT sum(amount) as tot2 from fakta_pendapatan";
$tot2 = mysqli_query($mysqli, $sql3);
$tot_penj = mysqli_fetch_assoc($tot2);

//3. Total Judul Film
$sql4 = "SELECT count(film_id) as tot_jud_film from film";
$tot3 = mysqli_query($mysqli, $sql4);
$tot_jud_film = mysqli_fetch_assoc($tot3)


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | Dashboard</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.min.css">
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/modules/data.js"></script>
  <script src="https://code.highcharts.com/modules/drilldown.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>
  <link rel="stylesheet" href="/drilldown.css" />
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.js">

  </script>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
  <div class="wrapper">

    <!-- Preloader -->
    <div class="preloader flex-column justify-content-center align-items-center">
      <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
    </div>

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <!-- <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li> -->
      </ul>

      <!-- Right navbar links -->
      <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
        <li class="nav-item">
          <a class="nav-link" data-widget="navbar-search" href="#" role="button">
            <i class="fas fa-search"></i>
          </a>

        </li>

        <!-- Messages Dropdown Menu -->

        <li class="nav-item">
          <a class="nav-link" data-widget="fullscreen" href="#" role="button">
            <i class="fas fa-expand-arrows-alt"></i>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-widget="control-sidebar" data-controlsidebar-slide="true" href="#" role="button">
            <i class="fas fa-th-large"></i>
          </a>
        </li>
      </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="index3.html" class="brand-link">
        <img src="dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <img src="dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
          </div>
          <div class="info">
            <a href="#" class="d-block">Dhimas Ardhi Maulana Saputra</a>
          </div>
        </div>



        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
            <li class="nav-item menu-open">
              <a href="#" class="nav-link active">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>
                  Dashboard
                  <i class="right fas fa-angle-left"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="customer.php" class="nav-link active">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Customer Chart</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="finance.php" class="nav-link active">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Finance Chart</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="film.php" class="nav-link active">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Film Chart</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="stores.php" class="nav-link active">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Stores Chart</p>
                  </a>
                </li>
              </ul>
            </li>

          </ul>
        </nav>
        <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0">Dashboard</h1>
            </div><!-- /.col -->
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="#">Home</a></li>
                <li class="breadcrumb-item active">Dashboard v1</li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <!-- Small boxes (Stat box) -->
          <div class="row">
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-info">
                <div class="inner">
                  <h2> <?php echo $jml_cust['jml_cust']; ?> </h2>

                  <p>
                  <h3>Total Customers</h3>
                  </p>
                </div>
                <div class="icon">
                  <i class="ion ion-person-add"></i>

                </div>
                <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-success">
                <div class="inner">
                  <h2> <?php echo number_format($tot_penj['tot2'], 2, ',', '.'); ?> </h2>

                  <p>
                  <h3>Total Sales </h3>
                  </p>
                </div>
                <div class="icon">
                  <i class="ion ion-stats-bars"></i>
                </div>
                <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-6">
              <!-- small box -->
              <div class="small-box bg-warning">
                <div class="inner">
                  <h2><?php echo $tot_jud_film['tot_jud_film']; ?></h2>

                  <p>
                  <h3>Total Judul Film</h3>
                  </p>
                </div>
                <div class="icon">
                  <i class="ion ion-bag"></i>
                </div>
                <!-- <a href="#" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a> -->
              </div>
            </div>

          </div>
          <!-- /.row -->
          <!-- Main row -->
          <div class="row">
            <!-- Left col -->
            <section class="col-lg-7 connectedSortable">
              <!-- Custom tabs (Charts with tabs)-->
              <div>
                <div>


                </div><!-- /.card-header -->
                <div class="card-body">
                  <div class="tab-content p-0">
                    <!-- Morris chart - Sales -->
                    <div id="revenue-chart" style="position: relative; height: 800px; width: 800px;">
                      <!-- <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas> -->
                      <figure class="highcharts-figure">
                        <div id="container"></div>

                        <p class="highcharts-description">

                        </p>
                        <div>
                          <iframe name="mondrian" src="http://localhost:8080/mondrian/index.html" style="height:800px ;width:800px; border:none; text-align:center;""></iframe> 
                      </div>
                    </figure>

                    
                    
                    
                    <script type=" text/javascript">
                            // Create the chart
                            Highcharts.chart('container', {
                            chart: {
                            type: 'pie'
                            },
                            title: {
                            text: 'Persentase Nilai Penjualan (WH Sakila) - Semua Kategori'
                            },
                            subtitle: {
                            text: 'Klik di potongan kue untuk melihat detil nilai penjualan kategori berdasarkan bulan'
                            },

                            accessibility: {
                            announceNewData: {
                            enabled: true
                            },
                            point: {
                            valueSuffix: '%'
                            }
                            },

                            plotOptions: {
                            series: {
                            dataLabels: {
                            enabled: true,
                            format: '{point.name}: {point.y:.1f}%'
                            }
                            }
                            },

                            tooltip: {
                            headerFormat: '<span style="font-size:11px">{series.name}</span><br>',
                            pointFormat: '<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br />'
                            },

                            series: [
                            {
                            name: "Pendapatan By Kategori",
                            colorByPoint: true,
                            data:
                            <?php
                            //TEKNIK GAK JELAS :D

                            $datanya =  $json_all_kat;
                            $data1 = str_replace('["', '{"', $datanya);
                            $data2 = str_replace('"]', '"}', $data1);
                            $data3 = str_replace('[[', '[', $data2);
                            $data4 = str_replace(']]', ']', $data3);
                            $data5 = str_replace(':', '" : "', $data4);
                            $data6 = str_replace('"name"', 'name', $data5);
                            $data7 = str_replace('"drilldown"', 'drilldown', $data6);
                            $data8 = str_replace('"y"', 'y', $data7);
                            $data9 = str_replace('",', ',', $data8);
                            $data10 = str_replace(',y', '",y', $data9);
                            $data11 = str_replace(',y : "', ',y : ', $data10);
                            echo $data11;
                            ?>

                            }
                            ],
                            drilldown: {
                            series: [

                            <?php
                            //TEKNIK CLEAN
                            echo $string_data;

                            ?>



                            ]
                            }
                            });
                            </script>


                        </div>

                    </div>
                  </div><!-- /.card-body -->
                </div>
                <!-- /.card -->



              </div>
              <!--/.direct-chat -->


            </section>
            <!-- /.Left col -->
            <!-- right col (We are only adding the ID to make the widgets sortable)-->


            <!-- right col -->
          </div>

          <!-- /.row (main row) -->
        </div><!-- /.container-fluid -->
      </section>
      <!-- /.content -->
    </div>


    <!-- /.content-wrapper -->
    <footer class="main-footer">
      <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
      All rights reserved.
      <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.2.0
      </div>
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->

  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>
  <!-- jQuery UI 1.11.4 -->
  <script src="plugins/jquery-ui/jquery-ui.min.js"></script>
  <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
  <script>
    $.widget.bridge('uibutton', $.ui.button)
  </script>
  <!-- Bootstrap 4 -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- ChartJS -->
  <script src="plugins/chart.js/Chart.min.js"></script>
  <!-- Sparkline -->
  <script src="plugins/sparklines/sparkline.js"></script>
  <!-- JQVMap -->
  <script src="plugins/jqvmap/jquery.vmap.min.js"></script>
  <script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
  <!-- jQuery Knob Chart -->
  <script src="plugins/jquery-knob/jquery.knob.min.js"></script>
  <!-- daterangepicker -->
  <script src="plugins/moment/moment.min.js"></script>
  <script src="plugins/daterangepicker/daterangepicker.js"></script>
  <!-- Tempusdominus Bootstrap 4 -->
  <script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
  <!-- Summernote -->
  <script src="plugins/summernote/summernote-bs4.min.js"></script>
  <!-- overlayScrollbars -->
  <script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.js"></script>
  <!-- AdminLTE for demo purposes -->
  <script src="dist/js/demo.js"></script>
  <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
  <script src="dist/js/pages/dashboard.js"></script>

</body>

</html>