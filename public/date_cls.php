<?php

/**
 * CLS Loans per Institution by Date Range
 * php version 8.1
 *
 * @category Alma
 * @package  WRLC
 * @author   Joel Shields <shields@wrlc.org>
 * @license  https://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/WRLC/interactive-alma-reports/docs/files/public-date-cls.html Documentation
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Get the API key from the environment
$api_key_interactive = $_ENV['API_KEY_INTERACTIVE'];

// Initialize the XML data as false
$xml_data = false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Form</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
</head>

<body>
<?php
// API URL
$url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=/shared/Washington%20Research%20Library%20Consortium%20(WRLC)%20Network/Reports/API/API%20rpt_clslibx%20by%20date%20range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22between%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Loan%20Date%22%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";
                        
// Parse the URL to extract the query string
$parsed_url = parse_url($url);
parse_str($parsed_url['query'], $query_params);

// Get the 'path' parameter and decode it for display
$path_value = urldecode($query_params['path']);

// Display the report path it as an <h2>

echo '<p>
  <button class="btn btn-info btn-sm mt-3 ml-5" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
    Report Info
  </button>
</p>
<div class="collapse" id="collapseExample">
  <div class="card card-body"><p><strong>Analytics Path:</strong> 
   '. htmlspecialchars($path_value) .'</p>';
  // <p><a class="btn mt-3 btn-sm btn-info" style="width:60px;" target="_blank" href="'.$url.'">XML</a></p>
 echo ' </div>
</div>';
?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h4 class="text-center">CLS Loans per Institution by Date Range</h4>
                <form method="get" class="alert alert-primary text-center" id="dateForm" style="padding:20px;" action="">

                    <?php
                    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                        $start_date = htmlspecialchars($_GET['start_date']);
                        $end_date = htmlspecialchars($_GET['end_date']);
                        ?>
                        <div class="row offset-md-2">
                            <div class="mb-3 col-md-6" style="max-width: 200px;">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" value="<?php echo $start_date ?>" name="start_date" required>
                            </div>
                        
                            <div class="mb-3 col-md-6" style="max-width: 200px;">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" value="<?php echo $end_date ?>" name="end_date" required>
                            </div>
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="row offset-md-2">
                            <div class="mb-3 col-md-6" style="max-width: 200px;">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>

                            <div class="mb-3 col-md-6" style="max-width: 200px;">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>
                        <?php
                    }

                    $distinct_column3 = []; // to store unique values for the dropdown

                    // If the form is submitted with start_date and end_date, process the XML data.
                    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                        $start_date = htmlspecialchars($_GET['start_date']);
                        $end_date = htmlspecialchars($_GET['end_date']);
                        //  $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=/shared/Washington%20Research%20Library%20Consortium%20(WRLC)%20Network/Reports/API/API%20rpt_clslibx%20by%20date%20range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22between%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Loan%20Date%22%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";
                        $xml_data = file_get_contents($url);

                        if ($xml_data !== false) {
                            $xml = simplexml_load_string($xml_data);
                            if ($xml !== false) {
                                foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                                    // Collect unique Column3 values for the dropdown
                                    $col3_value = (string)$row->Column3;
                                    if (!in_array($col3_value, $distinct_column3)) {
                                        $distinct_column3[] = $col3_value;
                                    }
                                }
                            }
                        }
                    }
                    ?>
                    <!-- Dropdown to filter Column3 -->
                    <div class="mb-3 offset-md-4" style="width: 200px;">
                        <label for="filter_column3" class="form-label">Filter by Lending Institution</label>
                        <select class="form-control" id="filter_column3" name="filter_column3">
                            <?php
                            if (isset($_GET['filter_column3']) && $_GET['filter_column3'] != '') {
                                ?>
                                <option value="<?php echo $_GET['filter_column3'] ?>" selected =""><?php echo $_GET['filter_column3'] ?></option>
                                <?php
                            } else {
                                ?>
                                <option value="" selected="">All Institutions</option>
                                <?php
                            }
                            ?>
                            <option value="American University">American University</option>
                            <option value="American University Washington College of Law">American University Washington College of Law</option>
                            <option value="Catholic University">Catholic University</option>
                            <option value="GW Jacob Burns">GW Jacob Burns</option>
                            <option value="Gallaudet University">Gallaudet University</option>
                            <option value="George Mason University">George Mason University</option>
                            <option value="George Washington University">George Washington University</option>
                            <option value="Georgetown University">Georgetown University</option>
                            <option value="Howard University">Howard University</option>
                            <option value="Marymount University">Marymount University</option>
                            <option value="Shared Collections Facility">Shared Collections Facility</option>
                            <option value="UDC Law">UDC Law</option>
                            <option value="University of the District of Columbia">University of the District of Columbia</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <a href="date_cls.php" class="btn btn-danger">Clear</a>
                </form>



<!-- Spinner section, initially hidden -->
<div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
            <div class=" text-center justify-content-center">
                <div class="spinner-border mt-1  text-primary text-center" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <p>Loading data, please wait...</p>
            </div>
</div>







                <?php
                if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    $start_date = htmlspecialchars($_GET['start_date']);
                    $end_date = htmlspecialchars($_GET['end_date']);
                    $filter_column3 = isset($_GET['filter_column3']) ? htmlspecialchars($_GET['filter_column3']) : '';
                    ?>
                <h4 class='mt-5 text-center'>Results for Date Range: <?php echo $start_date ?> to <?php echo $end_date ?>

                    <?php
                    if (isset($_GET['filter_column3']) and $_GET['filter_column3'] != '') {
                        $university_filter = htmlspecialchars($_GET['filter_column3']);
                        ?>
                    for <?php echo $university_filter ?>
                        <?php
                    }
                    ?>
                </h4>
                <table class='table table-bordered table-hover'>
                    <thead>
                        <tr>
                            <th>Lending Institution</th>
                            <th>Material Type</th>
                            <th>Borrowing Institution</th>
                            <th>Loans</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    // Process the XML data again to display filtered results
                    if ($xml_data !== false) {
                        $xml = simplexml_load_string($xml_data);
                        if ($xml !== false) {
                            $total_loans = 0;
                            $displayed_column3 = []; // To track displayed Column3 values

                            foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                                $col3_value = (string)$row->Column3;
                                if ($filter_column3 == '' || $col3_value == $filter_column3) {
                                    $loans = (int)$row->Column4;
                                    $total_loans += $loans;

                                    // Only display unique Column3 values
                                    if (!in_array($col3_value, $displayed_column3)) {
                                        $displayed_column3[] = $col3_value;
                                        $column3_display = $col3_value;
                                    } else {
                                        $column3_display = ''; // Leave empty if it's already displayed
                                    }
                                    ?>
                        <tr>
                            <td><strong><?php echo $column3_display ?></strong></td>
                            <td><?php echo $row->Column2 ?></td>
                            <td><?php echo $row->Column1 ?></td>
                            <td class='text-end'><?php echo number_format($loans) ?></td>
                        </tr>
                                    <?php
                                }
                            }
                            // Display the total loans
                            ?>
                        <tr>
                            <td><strong>Total:</strong></td>
                            <td colspan='3' class='text-end'><strong><?php echo number_format($total_loans) ?></strong></td>
                        </tr>
                            <?php
                        }
                    }
                    ?>

                    </tbody>
                </table>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>

  <script>
        document.getElementById('dateForm').onsubmit = function() {
            // Show the spinner when form is submitted
            document.getElementById('loadingSpinner').style.display = 'block';
        };

        <?php if ($xmlData) : ?>
            // Hide the spinner when the table is loaded
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('resultTable').style.display = 'block';
        <?php endif; ?>
    </script>
</body>

</html>