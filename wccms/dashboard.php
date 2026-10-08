<!-- START dashboard -->

<?php

// Turn off error reporting
error_reporting(0);
// Turn on error reporting
//error_reporting(1);

include('setting/main-top-files.php'); // Added by salva TDR | 7.12.2022
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <?php
        include("controllers/dashboard.php");
        include("include/header-code.php");
   ?>
   <!-- Ensure Bootstrap CSS is included -->
 <!--  <?php require_once __DIR__ . '/include/bootstrap-css.php'; ?> -->
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <script type="text/javascript">
        var counts = <?php echo json_encode($allCountsDataBySection); ?>;
        var sectionNames = <?php echo json_encode($sectionNames); ?>;
        console.log(counts);
    </script>
<style>
    .shortcuticons .far {
        font-size:26px;
    }
</style>
<section id="container" data-top="dashboard|">
      <?php
      include("include/header.php");
      include("include/sidebar.php");
      ?>
      <!--sidebar end-->
      <!--main content start-->
    <section id="main-content">
        <section class="wrapper">
            <!-- Container for the dynamic cards -->
            <div class="row state-overview d-flex justify-content-left">
               <div class="col-12"></div>

                <div class="col-sm-12 col-md-12">
                    <div class="block-flat" style="padding:0px 0px">
                        <div class="block-flat">
                            <div class="content">
                                <div class="row dash-cols">
                                    <div class="col-sm-12 col-md-12">

                                            <ul class="nav nav-tabs">
                                                <li class="nav-item">
                                                    <a class="nav-link active" href="#profile" data-bs-toggle="tab" role="tab" aria-controls="profile" aria-selected="true">Welcome</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" href="#stats" data-bs-toggle="tab" role="tab" aria-controls="stats" aria-selected="false">Stats</a>
                                                </li>
                                            </ul>

                                            <div class="tab-content mt-3">
                                                <div class="tab-pane fade show active" id="profile">
                                                    <h2 class="hthin">Welcome to your Web Site Management</h2>
                                                    <p></p>
                                                    <div class="row">

                                                        <div class="col-md-5 col-sm-6">
                                                            <h5>Truska CMS</h3>
                                                            <h6>Site: <?php echo $prefs["prefCompanyName"];?></h4>
                                                            <h6>URL: <?php echo $_SERVER['SERVER_NAME'];?></h5>
                                                            <h6>User: <?php echo $user["username"];?></h5>
                                                        </div>

                                                        <div class="col-md-5 col-sm-6">
                                                            <div class="row">
                                                                <div class='col-6 col-md-4  img-fluid'>
                                                                    <img src='../filestore/images/content/<?php echo $user["image"];?>' style='max-width:150px; '>
                                                                </div>
                                                                <div class='col-6 col-md-4 img-fluid text-align:left'>
                                                                    <img src='../filestore/images/logos/icon.png' style='max-width:150px; '>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-12 col-sm-12">
                                                            <h3>Short Cuts</h3>

                                                            <div class="row ">
                                                                <div class="col-4 col-md-4">
                                                                    <h5>Booking Reports</h5>
                                                                </div>
                                                                <div class="col-4 col-md-2" style="background-color:#b2ffb2; ">
                                                                    <h5>TANKER Reports</h5>
                                                                </div>
                                                            </div>

                                                            <div class="row shortcuticons">
                                                                <div class="col-6 col-md-2 ">
                                                                    <p><a href="/wccms/report_bookingv4.php?frm=1&id=7" target="_blank"> <i class="far fa-calendar-day"></i> TODAY</a></p>

                                                                    <p><a href="/wccms/report_bookingv4.php?frm=1&id=1" target="_blank"> <i class="far fa-calendar-week"></i> THIS WEEK</a></p>
                                                                </div>

                                                            

                                                                <div class="col-6 col-md-2">

                                                                    <p><a href="/wccms/report_bookingv4.php?frm=1&id=2" target="_blank"> <i class="far fa-calendar-plus"></i> NEXT WEEK</a></p>
                                                                    <p><a href="/wccms/report_bookingv4.php?frm=1&id=4" target="_blank"> <i class="far fa-calendar-alt"></i> CURRENT MONTH</a></p>
                                                                
                                                                </div>

                                                            
                                                            
                                                                <div class="col-6 col-md-2" style="background-color:#b2ffb2; ">
                                                                    <!-- TANKERS -->

                                                                    <?php
                                                                        // Ensure UK weeks/dates
                                                                        date_default_timezone_set('Europe/London');
                                                                        $thisMon = date('Y-m-d', strtotime('monday this week'));
                                                                        $thisSat = date('Y-m-d', strtotime('sunday this week'));
                                                                        $nextMon = date('Y-m-d', strtotime('monday next week'));
                                                                        $nextSun = date('Y-m-d', strtotime('sunday next week'));
                                                                    ?>
                                                                    <p>
                                                                        <a href="#" onclick="document.getElementById('tankQuick').submit(); return false;" target="_blank">
                                                                            <i class="far fa-calendar-week"></i> THIS WEEK
                                                                        </a>
                                                                    </p>


                                                                
                                                                    <form id="tankQuick" method="POST" action="report_1T.php" target="_blank" style="display:none;">
                                                                        <input type="hidden" name="frm" value="2">
                                                                        <input type="hidden" name="id" value="11">
                                                                        <!-- Set your defaults -->
                                                                        <input type="hidden" name="fromDate" value="<?= $thisMon ?>">
                                                                        <input type="hidden" name="toDate"   value="<?= $thisSat ?>">
                                                                        <input type="hidden" name="customer[]" value="17"> 
                                                                    </form>

                                                                    <p>
                                                                        <a href="#" onclick="document.getElementById('tankQuick2').submit(); return false;" target="_blank">
                                                                            <i class="far fa-calendar-plus"></i> NEXT WEEK
                                                                        </a>
                                                                    </p>

                                                                    <form id="tankQuick2" method="POST" action="report_1T.php" target="_blank" style="display:none;">
                                                                        <input type="hidden" name="frm" value="2">
                                                                        <input type="hidden" name="id" value="11">
                                                                        <!-- Set your defaults -->
                                                                        <input type="hidden" name="fromDate" value="<?= $nextMon ?>">
                                                                        <input type="hidden" name="toDate"   value="<?= $nextSun ?>">
                                                                        <input type="hidden" name="customer[]" value="17"> 
                                                                    </form>
                                                                
                                                                </div>

                                                            </div>
                                                        </div>

                                                    </div>


                                                </div>                                         
                                            </div>


                                            <div class="tab-pane fade" id="stats">
                                                <div id="counts-container">
                                                </div>

                                                <script type="text/javascript">
                                                    document.addEventListener('DOMContentLoaded', function () {
                                                        var countsContainer = document.getElementById('counts-container');

                                                        Object.keys(counts).forEach(function (section) {
                                                            var sectionMainContainer = document.createElement('div');
                                                            sectionMainContainer.className = 'container my-4'; // Bootstrap 5 spacing utility

                                                            // Section Header
                                                            var sectionHeaderRow = document.createElement('div');
                                                            sectionHeaderRow.className = 'row mb-3'; // Bootstrap 5 row and margin utility
                                                            var sectionHeaderCol = document.createElement('div');
                                                            sectionHeaderCol.className = 'col-12';
                                                            var header = document.createElement('h2');
                                                            header.textContent = sectionNames[section] ? sectionNames[section] : 'Section ' + section;
                                                            sectionHeaderCol.appendChild(header);
                                                            sectionHeaderRow.appendChild(sectionHeaderCol);
                                                            sectionMainContainer.appendChild(sectionHeaderRow);

                                                            // Cards Row
                                                            var cardsRow = document.createElement('div');
                                                            cardsRow.className = 'row g-3'; // Bootstrap 5 gutter utility for spacing between cards
                                                            counts[section].forEach(function (countData) {
                                                                var colDiv = document.createElement('div');
                                                                colDiv.className = 'col-lg-3 col-md-6 d-flex'; // Ensure proper grid alignment

                                                                // Card
                                                                var cardSection = document.createElement('div');
                                                                cardSection.className = 'card shadow-sm w-100'; // Add shadow and make the card fill the column
                                                                var symbolDiv = document.createElement('div');
                                                                symbolDiv.className = 'card-header text-white'; // Use card-header for consistent styling
                                                                symbolDiv.style.backgroundColor = countData.colour;

                                                                var iElement = document.createElement('i');
                                                                iElement.className = 'fas ' + countData.symbol; // Ensure Font Awesome 5 compatibility
                                                                symbolDiv.appendChild(iElement);

                                                                var cardBody = document.createElement('div');
                                                                cardBody.className = 'card-body text-center'; // Center-align the content

                                                                var h1Element = document.createElement('h1');
                                                                h1Element.className = 'display-4'; // Use Bootstrap display utility for prominent numbers
                                                                h1Element.innerHTML = countData.number;

                                                                var pElement = document.createElement('p');
                                                                pElement.className = 'text-muted'; // Muted text for additional info
                                                                pElement.innerHTML = countData.name;

                                                                cardBody.appendChild(h1Element);
                                                                cardBody.appendChild(pElement);
                                                                cardSection.appendChild(symbolDiv);
                                                                cardSection.appendChild(cardBody);
                                                                colDiv.appendChild(cardSection);
                                                                cardsRow.appendChild(colDiv);
                                                            });

                                                            sectionMainContainer.appendChild(cardsRow);
                                                            countsContainer.appendChild(sectionMainContainer);
                                                        });
                                                    });
                                                </script>
                                            </div>
                                            <div class="tab-pane fade" id="stats">
                                                <div id="counts-container"></div>
                                                <script type="text/javascript">
                                                    document.addEventListener('DOMContentLoaded', function() {
                                                        var countsContainer = document.getElementById('counts-container');
                                                        Object.keys(counts).forEach(function(section) {
                                                            var sectionMainContainer = document.createElement('div');
                                                            sectionMainContainer.className = 'container my-3';
                                                            var sectionHeaderRow = document.createElement('div');
                                                            sectionHeaderRow.className = 'row';
                                                            var sectionHeaderCol = document.createElement('div');
                                                            sectionHeaderCol.className = 'col-12';
                                                            var header = document.createElement('h2');
                                                            header.textContent = sectionNames[section] ? sectionNames[section] : 'Section ' + section;
                                                            sectionHeaderCol.appendChild(header);
                                                            sectionHeaderRow.appendChild(sectionHeaderCol);
                                                            sectionMainContainer.appendChild(sectionHeaderRow);
                                                            var cardsRow = document.createElement('div');
                                                            cardsRow.className = 'row';
                                                            counts[section].forEach(function(countData) {
                                                                var colDiv = document.createElement('div');
                                                                colDiv.className = 'col-lg-3 col-md-6';
                                                                var cardSection = document.createElement('section');
                                                                cardSection.className = 'card';
                                                                var symbolDiv = document.createElement('div');
                                                                symbolDiv.className = 'symbol';
                                                                symbolDiv.style.backgroundColor = countData.colour;
                                                                var iElement = document.createElement('i');
                                                                iElement.className = 'fal ' + countData.symbol;
                                                                symbolDiv.appendChild(iElement);
                                                                var valueDiv = document.createElement('div');
                                                                valueDiv.className = 'value';
                                                                var h1Element = document.createElement('h1');
                                                                h1Element.className = 'count';
                                                                h1Element.innerHTML = countData.number;
                                                                var pElement = document.createElement('p');
                                                                pElement.innerHTML = countData.name;
                                                                valueDiv.appendChild(h1Element);
                                                                valueDiv.appendChild(pElement);
                                                                cardSection.appendChild(symbolDiv);
                                                                cardSection.appendChild(valueDiv);
                                                                colDiv.appendChild(cardSection);
                                                                cardsRow.appendChild(colDiv);
                                                            });
                                                            sectionMainContainer.appendChild(cardsRow);
                                                            countsContainer.appendChild(sectionMainContainer);
                                                        });
                                                    });
                                                </script>
                                            </div>
        

                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                
            </div>
        </section>
    </section>
      <?php include("include/footer-code.php"); ?>
</section>

<?php

$section = fetchUniqueSections();
$allCountsDataBySection = [];
$countsDataJson = json_encode($allCountsDataBySection);

if ($countsDataJson === false) {
    $countsDataJson = '[]';
}
?>

<!-- Output the counts to JavaScript -->

<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
<!-- END dashboard -->
