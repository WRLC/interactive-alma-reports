<?php

/**
 * WRLC Consortium Loans Filled by Patrons for Physical Item Requests
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

//Name the Document
$page_title = 'WRLC Consortium Loans Filled by Patrons
    for Physical Item Requests';
$page_title_with_underscores = str_replace(' ', '_', $page_title);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    // Page title as a variable for use with CSV file name
    $page_title = 'WRLC Consortium Loans Filled by Patrons
    for Physical Item Requests';
    $page_title_with_underscores = str_replace(' ', '_', $page_title); ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

</head>

<body>

    <?php
    // show errors.  Remove when ready for production
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Function to fetch XML data via cURL
    function fetchXMLData($startDate, $endDate, $location)
    {

        // Get the API key from the environment
        $api_key_interactive = $_ENV['API_KEY_INTERACTIVE'];

        // Build the URL with dynamic start date, end date, and location
        $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington%20Research%20Library%20Consortium%20(WRLC)%20Network%2FReports%2FAPI%2FAPI%20rpt_IPEDSytdlibx_GAborrower_2&limit=1000&col_names=false&apikey=' . $api_key_interactive . '&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22between%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Loan%20Date%22%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $startDate . '%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $endDate . '%3C/sawx:expr%3E%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22equal%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Details%22.%22Loans%20-%20Linked%20From%20Institution%20Name%22%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:string%22%3E' . urlencode($location) . '%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E';

        // Parse the URL to extract the query string
        $parsed_url = parse_url($url);
        parse_str($parsed_url['query'], $query_params);

        // Get the 'path' parameter and decode it for display
        $path_value = urldecode($query_params['path']);

        // Display the report path as an <h2>
        echo '<p>
  <button class="btn btn-info btn-sm mt-3 ml-5" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">Report Info</button>
</p>
<div class="collapse" id="collapseExample"><div class="card card-body"><p><strong>Analytics Path:</strong>' . htmlspecialchars($path_value) . '</p></div>
</div>';

        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // Execute cURL and get the response
        $output = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);

        // Parse the XML response
        return simplexml_load_string($output);
    }

    // Generate CSV from XML data
    function generateCSV($xmlData)
    {

        $csvFileName = 'export.csv';
        $csvFilePath = __DIR__ . '/' . $csvFileName;

        // Open file for writing
        $file = fopen($csvFilePath, 'w');

        // Add the CSV headers
        fputcsv($file, ['Lender Institution', 'Type', 'Total']);

        // Add data rows
        foreach ($xmlData->QueryResult->ResultXml->rowset->Row as $row) {
            fputcsv($file, [
                (string)$row->Column3, // Lender Institution
                (string)$row->Column2, // Type
                (float)$row->Column4  // Total
            ]);
        }

        // Close file
        fclose($file);

        return $csvFileName;
    }

    // Get form data
    $startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
    $endDate = isset($_POST['end_date']) ? $_POST['end_date'] : '';
    $location = isset($_POST['location']) ? $_POST['location'] : '';
    $xmlData = null;
    $csvFileName = '';

    if ($startDate && $endDate && $location) {
        $xmlData = fetchXMLData($startDate, $endDate, $location);
        // Generate CSV if we have XML data
        if ($xmlData) {
            $csvFileName = generateCSV($xmlData);
        }
    }
    ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 mt-4">
                <h2 class="text-center"><?php echo $page_title; ?></h2>
                <div class="card">
                    <div class="card-header">
                        <h3>Select Date Range and Location</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="dateForm">
                            <div class="mb-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <select class="form-control" id="location" name="location" required>
                                    <option value="" disabled selected>Select a location</option>
                                    <option value="American University" <?php if ($location == "American University") echo 'selected'; ?>>American University</option>
                                    <option value="American University Washington College of Law" <?php if ($location == "American University Washington College of Law") echo 'selected'; ?>>American University Washington College of Law</option>
                                    <option value="American University Washington College of Law Library" <?php if ($location == "American University Washington College of Law Library") echo 'selected'; ?>>American University Washington College of Law Library</option>
                                    <option value="Catholic University of America" <?php if ($location == "Catholic University of America") echo 'selected'; ?>>Catholic University of America</option>
                                    <option value="Gallaudet University" <?php if ($location == "Gallaudet University") echo 'selected'; ?>>Gallaudet University</option>
                                    <option value="George Mason University Libraries" <?php if ($location == "George Mason University Libraries") echo 'selected'; ?>>George Mason University Libraries</option>
                                    <option value="George Washington University" <?php if ($location == "George Washington University") echo 'selected'; ?>>George Washington University</option>
                                    <option value="George Washington University Himmelfarb Health Sciences Library" <?php if ($location == "George Washington University Himmelfarb Health Sciences Library") echo 'selected'; ?>>George Washington University Himmelfarb Health Sciences Library</option>
                                    <option value="George Washington University Jacob Burns Law Library" <?php if ($location == "George Washington University Jacob Burns Law Library") echo 'selected'; ?>>George Washington University Jacob Burns Law Library</option>
                                    <option value="Georgetown University" <?php if ($location == "Georgetown University") echo 'selected'; ?>>Georgetown University</option>
                                    <option value="Georgetown University Law Library" <?php if ($location == "Georgetown University Law Library") echo 'selected'; ?>>Georgetown University Law Library</option>
                                    <option value="Howard University" <?php if ($location == "Howard University") echo 'selected'; ?>>Howard University</option>
                                    <option value="Marymount University" <?php if ($location == "Marymount University") echo 'selected'; ?>>Marymount University</option>
                                    <option value="Shared Collections Facility" <?php if ($location == "Shared Collections Facility") echo 'selected'; ?>>Shared Collections Facility</option>
                                    <option value="University of the District of Columbia" <?php if ($location == "University of the District of Columbia") echo 'selected'; ?>>University of the District of Columbia</option>
                                    <option value="University of the District of Columbia, Law School" <?php if ($location == "University of the District of Columbia, Law School") echo 'selected'; ?>>University of the District of Columbia, Law School</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button> <a class="btn btn-danger" href="">Clear</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spinner section, initially hidden -->
        <div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
            <div class=" text-center justify-content-center">
                <div class="spinner-border mt-1  text-primary text-center" role="status">
                    <span class="visually-hidden"></span>
                </div>
                <p>Loading data, please wait...</p>
            </div>
        </div>
        <!-- End Spinner section, initially hidden -->

        <!---- Display results ----->
        <?php if ($xmlData): ?>
            <div class="row justify-content-center mt-5">
                <div class="col-lg-8">
                    <h2 class="text-center">WRLC Consortium Loans Filled by
                        <?php echo (string)$xmlData->QueryResult->ResultXml->rowset->Row[0]->Column1; ?> Patrons</h2>
                    <h4 class="text-center">for physical item requests</h4>
                    <p>
                        <?php if ($startDate && $endDate && $location) {
                            echo "<p class='text-center'>Start Date: $startDate - End Date: $endDate</p>";
                        } ?>
                    <table class="table table-striped table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th>Lender Institution</th>
                                <th>Type</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $totalSum = 0; // Initialize the total sum of Column4
                            foreach ($xmlData->QueryResult->ResultXml->rowset->Row as $row):
                                $column4Value = (float)$row->Column4; // Convert Column4 value to float for summation
                                $totalSum += $column4Value; // Sum up Column4 values
                            ?>
                                <tr>
                                    <td><?php echo (string)$row->Column3; ?></td>
                                    <td><?php echo (string)$row->Column2; ?></td>
                                    <td><?php echo $column4Value; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Display the sum of Column4 -->
                    <div class="alert alert-info text-center">
                        <strong>Total: </strong><?php echo $totalSum; ?>
                    </div>

                    <!-- Provide a download link for the CSV -->
                    <?php if ($csvFileName): ?>
                        <div class="text-center mt-3">
                            <a href="<?php echo $csvFileName; ?>" class="btn btn-success">Download CSV</a>
                        </div><br />
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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

        <?php if ($xmlData): ?>
            // Hide the spinner when the table is loaded
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('resultTable').style.display = 'block';
        <?php endif; ?>
    </script>
</body>

</html>