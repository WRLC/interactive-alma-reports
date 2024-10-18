<?php

/**
 * Number of Loans per Institution by Date Range
 * php version 8.1
 *
 * @category Alma
 * @package  WRLC
 * @author   Joel Shields <shields@wrlc.org>
 * @license  https://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/WRLC/interactive-alma-reports/docs/files/public-date.html Documentation
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// Get the API key from the environment
$api_key_interactive = $_ENV['API_KEY_INTERACTIVE'];


// Check if CSV download is requested
if (isset($_GET['download_csv']) && $_GET['download_csv'] == 'true') {
    if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
        // Retrieve start and end dates
        $start_date = htmlspecialchars($_GET['start_date']);
        $end_date = htmlspecialchars($_GET['end_date']);

        // API URL to fetch the XML data
        $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington+Research+Library+Consortium+%28WRLC%29+Network%2FReports%2FCannedReports%2FNumber+of+loans+per+institution+by+date+range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20op=%22between%22%20xsi:type=%22sawx:comparison%22%3E%20%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Date%20Key%22%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";

        // Fetch the XML data
        $xml_data = file_get_contents($url);
        if ($xml_data !== false) {
            $xml = simplexml_load_string($xml_data);
            if ($xml !== false) {
                // Set headers for CSV download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="loans_report.csv"');

                // Open output stream for CSV
                $output = fopen('php://output', 'w');

                // Add CSV headers
                fputcsv($output, ['Institution', 'Loans']);

                // Loop through XML data and write to CSV
                foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                    $loans = (int)$row->Column3;
                    fputcsv($output, [$row->Column1, $loans]);
                }

                // Close the output stream
                fclose($output);
                exit;
            }
        }
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
    <?php
    // API URL
    $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington+Research+Library+Consortium+%28WRLC%29+Network%2FReports%2FCannedReports%2FNumber+of+loans+per+institution+by+date+range&limit=1000&col_names=true&apikey=" . $api_key_interactive . "&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20op=%22between%22%20xsi:type=%22sawx:comparison%22%3E%20%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Date%20Key%22%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";

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
   ' . htmlspecialchars($path_value) . '</p>';
    // <p><a class="btn mt-3 btn-sm btn-info" style="width:60px;" target="_blank" href="'.$url.'">XML</a></p>
    echo ' </div>
</div>';
    ?>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">
                <h4 class="text-center">Number of Loans per Institution by Date Range</h4>
                <h6 class="text-center"> (Not In House)</h6>
                <form method="get" class="alert alert-primary text-center" id="dateForm" style="padding:20px;" action="">
                    <div class="row offset-md-2">
                        <div class="mb-3" style="width: 200px;">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="mb-3" style="width: 200px;">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button> <a href="date.php" class="btn btn-danger">Clear</a>
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
                <!-- End Spinner section, initially hidden -->
                 
                <?php
                // Check if form is submitted
                if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
                    // Retrieve start and end dates
                    $start_date = htmlspecialchars($_GET['start_date']);
                    $end_date = htmlspecialchars($_GET['end_date']);

                    // Display the start and end dates
                ?>
                    <h4 class='mt-5'>Selected Dates:</h4>
                    <p><strong>Start Date:</strong> <?php echo $start_date ?></p>
                    <p><strong>End Date:</strong> <?php echo $end_date ?></p>

                    <?php
                    // Fetch XML data from the URL
                    $xml_data = file_get_contents($url);

                    // Check if data was successfully fetched
                    if ($xml_data === false) {
                    ?>
                        <p class='text-danger'>Failed to retrieve data from the API.</p>
                        <?php
                    } else {
                        // Parse the XML data
                        $xml = simplexml_load_string($xml_data);
                        if ($xml === false) {
                        ?>
                            <p class='text-danger'>Failed to parse XML data.</p>
                        <?php
                        } else {
                            // Initialize the sum variable
                            $total_loans = 0;

                            // Display the data in a table
                        ?>
                            <h4 class='mt-5'>Results:</h4>

                            <table style='max-width:800px;' class='table table-bordered table-hover'>
                                <thead>
                                    <tr>
                                        <th>Institution</th>
                                        <th style='max-width:200px;'>Loans</th>
                                    </tr>
                                </thead>
                                <tbody>






                                    <?php
                                    foreach ($xml->QueryResult->ResultXml->rowset->Row as $row) {
                                        // Convert Column3 to an integer and add to the total
                                        $loans = (int)$row->Column3;
                                        $total_loans += $loans;

                                        // Format Column3 as an integer with commas
                                        $formatted_column3 = number_format($loans);
                                    ?>
                                        <tr>
                                            <td><?php echo $row->Column1 ?></td>
                                            <td class='text-end'><?php echo $formatted_column3 ?></td>
                                        </tr>
                                    <?php
                                    }
                                    // Display the total in the last row
                                    ?>
                                    <tr>
                                        <td><strong>Total:</strong></td>
                                        <td class='text-end'><strong><?php echo number_format($total_loans) ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                <?php
                        }
                    }
                }
                ?>

                <?php
                // After the table displaying results
                if (isset($xml)) {
                    echo '<div class="text-center mt-3"><form method="get">';
                    echo '<input type="hidden" name="start_date" value="' . $start_date . '">';
                    echo '<input type="hidden" name="end_date" value="' . $end_date . '">';
                    echo '<input type="hidden" name="download_csv" value="true">';
                    echo '<button type="submit" class="btn btn-success text-center mt-3 mb-5">Download CSV</button>';
                    echo '</form></div>';
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

        <?php if ($xmlData): ?>
            // Hide the spinner when the table is loaded
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('resultTable').style.display = 'block';
        <?php endif; ?>
    </script>
</body>

</html>