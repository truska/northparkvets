<?php
include('setting/main-top-files.php');
include('include/header-code.php');

// Calculate defaults: Last month and appropriate year
$currentMonth = date('m');
$currentYear = date('Y');
// used for default to last month
/*
$lastMonth = $currentMonth - 1;
if ($lastMonth == 0) {
    $lastMonth = 12;
    $currentYear -= 1;
}
$defaultMonth = $lastMonth;
$defaultYear = $currentYear;
*/
$months = [
    1 => "January", 2 => "February", 3 => "March", 4 => "April", 5 => "May", 6 => "June",
    7 => "July", 8 => "August", 9 => "September", 10 => "October", 11 => "November", 12 => "December"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Billing Reports Selector</title>
<?php require_once __DIR__ . '/include/bootstrap-css.php'; ?>
</head>
<body>
<?php require_once __DIR__ . '/include/dev-banner.php'; ?>
    <?php include("include/header.php"); include("include/sidebar.php"); ?>

  <?php
    $vetNameOptions = getVetOptions($conn);                // Vet name options
?>
<style>
    .btn {
        margin-top:20px;
    }
</style>
    <section id="main-content">
        <section class="wrapper site-min-height">
            <!-- Retaining Existing Content -->
            <div class="row">
                <div class="col-12 col-md-8 mainbody">
                    <?php
                        echo "<h2>Report Billing Selector</h2>" ;
                    ?>
                </div>
                <div class="col-12 col-md-4">

                
                    <p>Area for more info</p>
                </div>
            </div>

            <!-- Billing Selector Section -->
            <div class="row">

                <!-- Report: Billing -->
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <strong>BILLING</strong>
                        </div>
                        <div class="card-body">
                            <form action="report_billingv4.php" method="get">

                                <?php
                                // Fetch customer list from database
                                $query = "SELECT id, name FROM npe_customer WHERE showonweb = 'Yes' ORDER BY name";
                                $result = mysqli_query($conn, $query);
                                $customers = [];
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $customers[] = $row;
                                }
                                ?>

                                <div class="form-group">
                                    <label for="customer">Customer</label>
                                    <select id="customer" name="c" class="form-control">
                                        <option value="all">All</option>
                                        <?php foreach ($customers as $customer): ?>
                                            <option value="<?= $customer['id'] ?>"><?= htmlspecialchars($customer['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>


                                <div class="form-group">
                                    <label for="month">Month</label>
                                    <select id="month" name="m" class="form-control">
                                        <?php foreach ($months as $num => $name): ?>
                                            <option value="<?= $num ?>" <?= ($num == $currentMonth) ? 'selected' : '' ?>>
                                                <?= $name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <select id="year" name="y" class="form-control">
                                        <?php for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($i == $currentYear) ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Additional cards for other reports can go here -->

                <!-- Report: Vet Turnover -->
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <strong>Vet Turnover</strong>
                        </div>
                        <div class="card-body">
                            <form action="report_billing_vetv4.php" method="get">

                                <div class="form-group">
                                    <label for="month">Month</label>
                                    <select id="month" name="m" class="form-control">
                                        <?php foreach ($months as $num => $name): ?>
                                            <option value="<?= $num ?>" <?= ($num == $currentMonth) ? 'selected' : '' ?>>
                                                <?= $name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="year">Year</label>
                                    <select id="year" name="y" class="form-control">
                                        <?php for ($i = $currentYear - 2; $i <= $currentYear + 1; $i++): ?>
                                            <option value="<?= $i ?>" <?= ($i == $currentYear) ? 'selected' : '' ?>>
                                                <?= $i ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>



                                <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                            </form>
                        </div>
                    </div>
                </div>


                <!-- Report: NEW VET TURNOVER -->
                <div class="col-md-6 col-lg-3">

                    <div class="card mb-3">
                        <div class="card-header">
                            <strong>Vet Turnover <span style="color:red;">Under Development</span></strong>
                        </div>

                        <?php
                            $firstDay = date('Y-m-01'); // First day of current month
                            $lastDay = date('Y-m-t'); // Last day of current month
                        ?>

                        <div class="card-body">
                            <form action="report_billing_vet_b_v4.php" method="get">

                                <h5 class="card-title">Date Range</h5>
                                <div class="form-group">
                                    <label for="fromDate">From Date</label>
                                    <input type="date" id="fromDate" name="fromDate" class="form-control" value="<?= $firstDay ?>">
                                </div>
                                <div class="form-group">
                                    <label for="toDate">To Date</label>
                                    <input type="date" id="toDate" name="toDate" class="form-control" value="<?= $lastDay ?>">
                                </div>

                                <div class="form-group">
                                    <label for="vetName">Select Vet</label>
                                    <select id="vetName" name="vetName[]" class="form-control" multiple size="<?php echo $showrows; ?>">
                                        <option value="ALL" selected>ALL Vets</option> <!-- Default to ALL -->
                                        <?php foreach ($vetNameOptions as $option): ?>
                                            <option value="<?= htmlspecialchars($option['value']) ?>"><?= htmlspecialchars($option['label']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>


                                 <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    
                            </form>
                        </div>                            
                        <!-- </div> -->

                    </div>

            </div>
        </section>
    </section>

    <?php include("include/footer-code.php"); ?>
<?php require_once __DIR__ . '/include/bootstrap-js.php'; ?>
</body>
</html>
