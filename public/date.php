<?php
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Date Range Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 offset-md-3">

                <h4 class="text-center">Number of Loans per Institution by Date Range</h4>
                <h6 class="text-center"> (Not In House)</h6>
                <form method="get" class="alert alert-primary text-center" style="padding:20px;" action="">
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
                    // API URL
                    $url = "https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?path=%2Fshared%2FWashington+Research+Library+Consortium+%28WRLC%29+Network%2FReports%2FCannedReports%2FNumber+of+loans+per+institution+by+date+range&limit=1000&col_names=true&apikey=".$api_key_interactive."&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%20%3E%3Csawx:expr%20op=%22between%22%20xsi:type=%22sawx:comparison%22%3E%20%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Loan%20Date%22.%22Date%20Key%22%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $start_date . "%3C/sawx:expr%3E%20%3Csawx:expr%20xsi:type=%22xsd:date%22%3E" . $end_date . "%3C/sawx:expr%3E%3C/sawx:expr%3E%3C/sawx:expr%3E";
                    ?>
                    <div><a class="btn btn-success" href="<?php echo $url ?>">XML Link</a></div>
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
                            <a target="_blank" href="<?php echo $url ?>">Link to XML</a>
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
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>