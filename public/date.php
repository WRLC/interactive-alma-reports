<?php

/**
 * Number of Loans per Institution by Date Range
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Get the API key from the environment
$api_key_interactive = $_ENV['API_KEY_INTERACTIVE'];

    // API URL to fetch the XML data
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    // Retrieve start and end dates
    $start_date = htmlspecialchars($_GET['start_date']);
    $end_date = htmlspecialchars($_GET['end_date']);

    $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington+Research+Library+Consortium+%28WRLC%29+Network%2FReports%2FCannedReports%2FNumber+of+loans+per+institution+by+date+range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20op=%22between%22%20xsi:type=%22sawx:comparison%22%3E%20%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Date%20Key%22%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";
} else {
    $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington+Research+Library+Consortium+%28WRLC%29+Network%2FReports%2FCannedReports%2FNumber+of+loans+per+institution+by+date+range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20op=%22between%22%20xsi:type=%22sawx:comparison%22%3E%20%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Date%20Key%22%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";
}
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


// Check if CSV download is requested
if (isset($_GET['download_csv']) && $_GET['download_csv'] == 'true') {
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        // Retrieve start and end dates
        $start_date = htmlspecialchars($_GET['start_date']);
        $end_date = htmlspecialchars($_GET['end_date']);

        // API URL to fetch the XML data
        $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington+Research+Library+Consortium+%28WRLC%29+Network%2FReports%2FCannedReports%2FNumber+of+loans+per+institution+by+date+range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20op=%22between%22%20xsi:type=%22sawx:comparison%22%3E%20%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Date%20Key%22%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";

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
    }
}
// End CSV code

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
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3 d-print-none">
            <h2 class="text-center">Number of Loans per Institution by Date Range</h2>
                    <h6 class="text-center"> (Not In House)</h6>
                    <div class="card">
                    <div class="card-header text-center">
                        <h5>Select Date Range</h5>
                    </div>
                    <div class="card-body">
                
                <form method="get" id="dateForm" class="text-center" style="padding:20px;" action="">


                <div class="row offset-md-1">
                    <div class="mb-3 col-md-6" style="max-width: 200px;">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    
                    <div class="mb-3 col-md-6" style="max-width: 200px;">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                  </div>



                   

                    <button type="submit" class="btn btn-primary">Submit</button> <a href="date.php" class="btn btn-danger">Clear</a>
                </form>
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

                <?php
                // Check if form is submitted
                if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    // Retrieve start and end dates
                    $start_date = htmlspecialchars($_GET['start_date']);
                    $end_date = htmlspecialchars($_GET['end_date']);

                    // Display the start and end dates
                    echo "<h4 class='mt-5'>Selected Dates:</h4>";
                    echo "<p><strong>Start Date:</strong> $start_date</p>";
                    echo "<p><strong>End Date:</strong> $end_date</p>";

                    // API URL
                    $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington+Research+Library+Consortium+%28WRLC%29+Network%2FReports%2FCannedReports%2FNumber+of+loans+per+institution+by+date+range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20op=%22between%22%20xsi:type=%22sawx:comparison%22%3E%20%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Date%20Key%22%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";

                    // Fetch XML data from the URL
                    $xml_data = file_get_contents($url);

                    // Check if data was successfully fetched
                    if ($xml_data === false) {
                        echo "<p class='text-danger'>Failed to retrieve data from the API.</p>";
                    } else {
                        // Parse the XML data
                        $xml = simplexml_load_string($xml_data);

                        if ($xml === false) {
                            echo "<p class='text-danger'>Failed to parse XML data.</p>";
                        } else {
                            // Initialize the sum variable
                            $total_loans = 0;

                            // Display the data in a table
                            echo "<h4 class='mt-5'>Results:</h4>";
                            echo "<table style='max-width:800px;' class='table table-bordered table-hover'>";
                            echo "<thead><tr><th>Institution</th><th style='max-width:200px;'>Loans</th></tr></thead>";
                            echo "<tbody>";
                            foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                                // Convert Column3 to an integer and add to the total
                                $loans = (int)$row->Column3;
                                $total_loans += $loans;

                                // Format Column3 as an integer with commas
                                $formatted_column3 = number_format($loans);
                                echo "<tr>";
                                echo "<td>{$row->Column1}</td>";
                                echo "<td class='text-end'>{$formatted_column3}</td>";
                                echo "</tr>";
                            }

                            // Display the total in the last row
                            echo "<tr>";
                            echo "<td><strong>Total:</strong></td>";
                            echo "<td class='text-end'><strong>" . number_format($total_loans) . "</strong></td>";
                            echo "</tr>";

                            echo "</tbody></table>";
                        }
                    }
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

        <?php if (isset($xml_data)) : ?>
            // Hide the spinner when the table is loaded
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('resultTable').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>