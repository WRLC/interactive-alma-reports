<?php

/**
 * CLS Loans Filled by Institution by Lender
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Get the API key from the environment
$api_key_interactive = $_ENV['API_KEY_INTERACTIVE'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $page_title = 'CLS Loans Filled by Institution by Lender';
    $page_title_with_underscores = str_replace(' ', '_', $page_title);
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Custom CSS to make the table flexible and fit at 100% width with 10px margins -->
    <style>
        /* Table layout fixed, full width with margins, and slightly reduced font size */
        .table-fixed {
           /* table-layout: fixed;
            width: calc(100% - 20px);
            margin-left: 10px;
            margin-right: 10px; */
            font-size: 0.85rem;
        }
        .table-fixed th,
        .table-fixed td {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        /* Allow the first column to wrap and display fully */
        .table-fixed th:first-child,
        .table-fixed td:first-child {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }
    </style>
</head>

<body>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to fetch XML data via cURL
function fetchXmlData(string $startDate, string $endDate, string $location, string $apikeyinteractive): SimpleXMLElement
{
    $apikey = $apikeyinteractive;

    // To select all libraries, $location is empty
    if ($location == 'all') {
        $location = '';
    }
    // URL for API
    $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington%20Research%20Library%20Consortium%20(WRLC)%20Network%2FReports%2FAPI%2FAPI%20-%20Interactive%20-%20WRLC%20Consortium%20Loans%20Filled%20by%20Lender%20Institution&limit=1000&col_names=false&apikey=' . $apikey . '&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22between%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Loan%20Date%22%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $startDate . '%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $endDate . '%3C/sawx:expr%3E%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22equal%22%3E%3Csawx:expr%20xsi:type=%22sawx:columnExpression%22%20formulaUse=%22code%22%20displayUse=%22display%22%3E%3Csaw:columnFormula%20formulaUse=%22display%22%3E%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Institution%22.%22Institution%20Name%22%3C/sawx:expr%3E%3C/saw:columnFormula%3E%3C/sawx:expr%3E%3Csawx:expr%20xsi:type=%22xsd:decimal%22%3E' . urlencode($location) . '%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E';

    // Parse the URL to extract the query string
    $parsedUrl = parse_url($url);
    parse_str($parsedUrl['query'], $queryParams);

    // Get the 'path' parameter and decode it for display
    $pathValue = urldecode($queryParams['path']);

    // Display the report path as an <h2>
    echo '<p>
            <button class="btn btn-info btn-sm mt-3 ml-5" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                Report Info
            </button>
         </p>
         <div class="collapse" id="collapseExample">
            <div class="card card-body">
                <p><strong>Analytics Path:</strong> ' . htmlspecialchars($pathValue) . '</p>
            </div>
         </div>';


    // Initialize cURL
    $curlHandler  = curl_init();
    curl_setopt($curlHandler, CURLOPT_URL, $url);
    curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);

    // Execute cURL and get the response
    $output = curl_exec($curlHandler);

    if (curl_errno($curlHandler)) {
        echo 'Curl error: ' . curl_error($curlHandler);
    }
        curl_close($curlHandler);


    if (curl_errno($curlHandler)) {
        echo 'Curl error: ' . curl_error($curlHandler);
    }
    curl_close($curlHandler);

    // Parse the XML response
    return simplexml_load_string($output);
}

// Generate CSV from XML data
function generateCSV(SimpleXMLElement $xmlData): string
{
    $csvFileName = 'export.csv';
    $csvFilePath = __DIR__ . '/' . $csvFileName;

    // Open file for writing
    $file = fopen($csvFilePath, 'w');

    // Add the CSV headers
    fputcsv($file, ['Lender Institution', 'Borrower Institution', 'Count', 'Total Count', 'Lender Total']);

    // Add data rows
    foreach ($xmlData->QueryResult->ResultXml->rowset->Row as $row) {
        fputcsv($file, [
            (string)$row->Column1, // Lender Institution
            (string)$row->Column2, // Borrower Institution
            (string)$row->Column4, // Count
            (string)$row->Column5, // Total Count
            (float)$row->Column7  // Lender Total
        ]);
    }

    // Close file
    fclose($file);

    return $csvFileName;
}

// Get form data
$startDate = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$endDate   = isset($_POST['end_date']) ? $_POST['end_date'] : '';
$location  = isset($_POST['location']) ? $_POST['location'] : '';

$xmlData    = null;
$csvFileName = '';

if ($startDate && $endDate && $location) {
    $xmlData = fetchXmlData($startDate, $endDate, $location, $api_key_interactive);
    // Generate CSV if we have XML data
    if ($xmlData) {
        $csvFileName = generateCSV($xmlData);
    }
}
?>


<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-6 mt-4 d-print-none">
            <h2 class="text-center"><?php echo $page_title; ?></h2>
            <div class="card">
                <div class="card-header text-center">
                    <h5>Select Date Range and Location</h5>
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
                            <label for="location" class="form-label">Lender Institution</label>
                            <select class="form-control" id="location" name="location" required>
                                <option value="8" disabled selected>Select a Lender Institution</option>
                                <option value="all" <?php if ($location == "all") {
                                    echo 'selected';
                                                    } ?>>All</option>
                                <option value="4102" <?php if ($location == "4102") {
                                    echo 'selected';
                                                     } ?>>American University</option>
                                <option value="4114" <?php if ($location == "4114") {
                                    echo 'selected';
                                                     } ?>>American University Washington College of Law</option>
                                <option value="4103" <?php if ($location == "4103") {
                                    echo 'selected';
                                                     } ?>>Catholic University of America</option>
                                <option value="8086" <?php if ($location == "8086") {
                                    echo 'selected';
                                                     } ?>>Catholic University of America Columbus School of Law</option>
                                <option value="4104" <?php if ($location == "4104") {
                                    echo 'selected';
                                                     } ?>>Gallaudet University</option>
                                <option value="4105" <?php if ($location == "4105") {
                                    echo 'selected';
                                                     } ?>>George Mason University Libraries</option>
                                <option value="4107" <?php if ($location == "4107") {
                                    echo 'selected';
                                                     } ?>>George Washington University</option>
                                <option value="4110" <?php if ($location == "4110") {
                                    echo 'selected';
                                                     } ?>>George Washington University Himmelfarb Health Sciences Library</option>
                                <option value="4112" <?php if ($location == "4112") {
                                    echo 'selected';
                                                     } ?>>George Washington University Jacob Burns Law Library</option>
                                <option value="4111" <?php if ($location == "4111") {
                                    echo 'selected';
                                                     } ?>>Georgetown University</option>
                                <option value="4113" <?php if ($location == "4113") {
                                    echo 'selected';
                                                     } ?>>Georgetown University Law Library</option>
                                <option value="4109" <?php if ($location == "4109") {
                                    echo 'selected';
                                                     } ?>>Howard University</option>
                                <option value="4106" <?php if ($location == "4106") {
                                    echo 'selected';
                                                     } ?>>Marymount University</option>
                                <option value="4617" <?php if ($location == "4617") {
                                    echo 'selected';
                                                     } ?>>Shared Collections Facility</option>
                                <option value="4108" <?php if ($location == "4108") {
                                    echo 'selected';
                                                     } ?>>University of the District of Columbia</option>
                                <option value="4118" <?php if ($location == "4118") {
                                    echo 'selected';
                                                     } ?>>University of the District of Columbia, Law School</option>
                                <option value="4101" <?php if ($location == "4101") {
                                    echo 'selected';
                                                     } ?>>Washington Research Library Consortium (WRLC) Network</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a class="btn btn-danger" href="">Clear</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Spinner section, initially hidden -->
    <div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
        <div class="text-center justify-content-center">
            <div class="spinner-border mt-1 text-primary text-center" role="status">
                <span class="visually-hidden"></span>
            </div>
            <p>Loading data, please wait...</p>
        </div>
    </div>
    <!-- End Spinner section -->

    <?php if ($xmlData) : ?>
        <div class="row justify-content-center mt-5">
            <div class="col-lg-16">
                <h2 class="text-center"><?php echo $page_title; ?> :</h2>
                <?php
                // Build a multidimensional array using Institution (Column1) as keys
                // and grouping values from Column4 by unique Borrower names (Column2).
                $totalSum = 0;
                $data = [];
                $uniqueBorrowers = [];

                foreach ($xmlData->QueryResult->ResultXml->rowset->Row as $row) {
                    $institution = (string)$row->Column1;
                    $borrower    = (string)$row->Column2;
                    $value       = (float)$row->Column4;
                    $totalSum   += $value;

                    if (!isset($data[$institution])) {
                        $data[$institution] = [];
                    }
                    if (!isset($data[$institution][$borrower])) {
                        $data[$institution][$borrower] = 0;
                    }
                    $data[$institution][$borrower] += $value;

                    // Collect unique borrower names
                    $uniqueBorrowers[$borrower] = $borrower;
                }

                // Sort unique borrower names for consistent column order.
                $uniqueBorrowers = array_values($uniqueBorrowers);
                sort($uniqueBorrowers);

                // Define the mapping for shortened column headers and first column values.
                $shortNames = [
                    "American University" => "AU",
                    "American University Washington College of Law" => "AU Law",
                    "Catholic University of America" => "CU",
                    "Catholic University of America Columbus School of Law" => "CU Law",
                    "Gallaudet University" => "GA",
                    "George Mason University Libraries" => "GM",
                    "George Washington University" => "GW",
                    "George Washington University Himmelfarb Health Sciences Library" => "GW HS",
                    "George Washington University Jacob Burns Law Library" => "GW Law",
                    "Georgetown University" => "GT",
                    "Georgetown University Law Library" => "GT Law",
                    "Howard University" => "HU",
                    "Marymount University" => "MU",
                    "Shared Collections Facility" => "SCF",
                    "University of the District of Columbia" => "UDC",
                    "University of the District of Columbia, Law School" => "UDC Law"
                ];
                ?>
                <table class="table table-striped table-bordered table-sm table-fixed">
                    <thead class="thead-dark">
                        <tr>
                            <th>Lender</th>
                            <?php foreach ($uniqueBorrowers as $borrower) : ?>
                                <th><?php echo htmlspecialchars(isset($shortNames[$borrower]) ? $shortNames[$borrower] : $borrower); ?></th>
                            <?php endforeach; ?>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $institution => $borrowersData) : ?>
                            <tr>
                                <td>
                                    <?php
                                    echo htmlspecialchars(isset($shortNames[$institution]) ? $shortNames[$institution] : $institution);
                                    ?>
                                </td>
                                <?php
                                $rowTotal = 0;
                                foreach ($uniqueBorrowers as $borrower) :
                                    $value = isset($borrowersData[$borrower]) ? $borrowersData[$borrower] : 0;
                                    $rowTotal += $value;
                                    ?>
                                    <td class="text-right">
                                        <?php echo $value ? number_format($value, 0) : ''; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-right"><strong><?php echo number_format($rowTotal, 0); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <!-- Display the overall total of Column4 -->
                <div class="alert alert-info text-center">
                    <strong>Total: </strong><?php echo number_format($totalSum, 0); ?>
                </div>

                <!-- Provide a download link for the CSV -->
                <?php if ($csvFileName) : ?>
                    <div class="text-center mt-3">
                        <a href="<?php echo $csvFileName; ?>" class="btn btn-success">Download CSV</a>
                    </div>
                    <br />
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
        // Show the spinner when the form is submitted.
        document.getElementById('loadingSpinner').style.display = 'block';
    };

    <?php if ($xmlData) : ?>
        // Hide the spinner when the table is loaded.
        document.getElementById('loadingSpinner').style.display = 'none';
    <?php endif; ?>
</script>
</body>

</html>