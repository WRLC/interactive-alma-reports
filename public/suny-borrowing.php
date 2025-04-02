<?php

/**
 * SUNY - Borrowing Requests
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$api_key_interactive = $_ENV['API_KEY_INTERACTIVE'] ?? '';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ------------------ FUNCTION TO FETCH XML DATA ------------------
function fetchXmlData(string $startDate, string $endDate, string $apikeyinteractive): ?SimpleXMLElement {
    $url = 'https://api-na.hosted.exlibrisgroup.com/almaws/v1/analytics/reports?'
         . 'path=%2Fshared%2FWashington%20Research%20Library%20Consortium%20(WRLC)%20Network%2FReports%2FAPI%2FAPI%20-%20rpt_TotalBorrowingSUNY'
         . '&limit=1000'
         . '&col_names=false'
         . '&apikey='.$apikeyinteractive
         . '&filter=%3Csawx:expr%20xsi:type=%22sawx:list%22%20op=%22containsAny%22'
         . '%20xmlns:saw=%22com.siebel.analytics.web/report/v1.1%22'
         . '%20xmlns:sawx=%22com.siebel.analytics.web/expression/v1.1%22'
         . '%20xmlns:xsi=%22http://www.w3.org/2001/XMLSchema-instance%22'
         . '%20xmlns:xsd=%22http://www.w3.org/2001/XMLSchema%22%3E%3Csawx:expr%3E'
         . '%3Csawx:expr%20xsi:type=%22sawx:comparison%22%20op=%22between%22%3E'
         . '%3Csawx:expr%20xsi:type=%22sawx:sqlExpression%22%3E%22Borrowing%20Creation%20Date%22.'
         . '%22Borrowing%20Creation%20Date%22%3C/sawx:expr%3E'
         . '%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $startDate . '%3C/sawx:expr%3E'
         . '%3Csawx:expr%20xsi:type=%22xsd:date%22%3E' . $endDate . '%3C/sawx:expr%3E%3C/sawx:expr%3E'
         . '%3C/sawx:expr%3E%3C/sawx:expr%3E';

          // Save URL to a global variable for display later
    $GLOBALS['pathurl'] = $url;

    $curlHandler = curl_init();
    curl_setopt($curlHandler, CURLOPT_URL, $url);
    curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curlHandler);
    if (curl_errno($curlHandler)) {
        echo 'Curl error: ' . curl_error($curlHandler);
    }
    curl_close($curlHandler);
    $xml = simplexml_load_string($output);
    return $xml === false ? null : $xml;
}

// ------------------ FUNCTION TO GENERATE RAW CSV ------------------
function generateCSV(SimpleXMLElement $xmlData): string {
    $csvFileName = 'export.csv';
    $csvFilePath = __DIR__ . '/' . $csvFileName;
    $file = fopen($csvFilePath, 'w');
    fputcsv($file, ['Institution', 'Request Type', 'Borrower', 'Borrowing Request Type', 'Avg Hours Request Created to Sent Material', 'Count']);
    if (isset($xmlData->QueryResult->ResultXml->rowset)) {
        foreach ($xmlData->QueryResult->ResultXml->rowset as $rowset) {
            if (isset($rowset->Row)) {
                foreach ($rowset->Row as $row) {
                    fputcsv($file, [
                        (string)$row->Column2,
                        (string)$row->Column1,
                        (string)$row->Column3,
                        (string)$row->Column4,
                        (float)$row->Column7,
                        (float)$row->Column8
                    ]);
                }
            }
        }
    }
    fclose($file);
    return $csvFileName;
}

// ------------------ GET FORM DATA ------------------
$startDate = $_POST['start_date'] ?? '';
$endDate   = $_POST['end_date'] ?? '';
$xmlData = null;
$csvFileName = '';
if ($startDate && $endDate) {
    $xmlData = fetchXmlData($startDate, $endDate, $api_key_interactive);
    if ($xmlData) {
        $csvFileName = generateCSV($xmlData);
    }
}

// ------------------ BUILD THE THREE-DIMENSIONAL PIVOT ------------------
// Group by:
//   - Column1: Lender Institution
//   - Column4: Request Status
//   - Column2: Request Type (3rd dimension)
// Pivot columns: distinct values from Column3 (summing Column8)
$pivotData = [];  // $pivotData[Column1][Column4][Column2][Column3] = sum(Column8)
$distinctCol3 = []; // For distinct Column3 values

// For Time, display (do not sum) the first encountered Column7 value for each (Column1, Column4, Column2) group.
$timeData = [];   // $timeData[Column1][Column4][Column2] = (int) Column7

if ($xmlData && isset($xmlData->QueryResult->ResultXml->rowset)) {
    foreach ($xmlData->QueryResult->ResultXml->rowset as $rowset) {
        if (isset($rowset->Row)) {
            foreach ($rowset->Row as $row) {
                $c1 = (string)$row->Column2;
                $reqType = (string)$row->Column1;
                $c3 = (string)$row->Column3;
                $status = (string)$row->Column4;
                $val = (float)$row->Column8;
                $time = (float)$row->Column7;
                if (!isset($pivotData[$c1])) {
                    $pivotData[$c1] = [];
                }
                if (!isset($pivotData[$c1][$status])) {
                    $pivotData[$c1][$status] = [];
                }
                if (!isset($pivotData[$c1][$status][$reqType])) {
                    $pivotData[$c1][$status][$reqType] = [];
                }
                if (!isset($pivotData[$c1][$status][$reqType][$c3])) {
                    $pivotData[$c1][$status][$reqType][$c3] = 0;
                }
                $pivotData[$c1][$status][$reqType][$c3] += $val;
                $distinctCol3[$c3] = true;
                if (!isset($timeData[$c1])) {
                    $timeData[$c1] = [];
                }
                if (!isset($timeData[$c1][$status])) {
                    $timeData[$c1][$status] = [];
                }
                if (!isset($timeData[$c1][$status][$reqType])) {
                    $timeData[$c1][$status][$reqType] = (int) round($time);
                }
            }
        }
    }
}
$distinctColumn3 = array_keys($distinctCol3);
sort($distinctColumn3);
// Force exactly 7 pivot columns
$desiredPivotCount = 7;
if (count($distinctColumn3) < $desiredPivotCount) {
    $distinctColumn3 = array_merge($distinctColumn3, array_fill(0, $desiredPivotCount - count($distinctColumn3), ""));
} elseif (count($distinctColumn3) > $desiredPivotCount) {
    $distinctColumn3 = array_slice($distinctColumn3, 0, $desiredPivotCount);
}

$page_title = 'SUNY - Borrowing Requests';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <style>
        .subtotal-row { background-color: #f8f9fa; font-weight: bold; }
        .grandtotal-row { background-color: #e2e3e5; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">

<?php
///////////// Display Path to Report ////////////////////
if(isset($pathurl)) {
 // Parse the URL to extract the query string
 $parsedUrl = parse_url($pathurl);
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
}
?>
    <!-- Date Range Form -->
    <div class="row justify-content-center">
        <div class="col-lg-6 mt-4 d-print-none">
            <h2 class="text-center"><?php echo $page_title; ?></h2>
            <div class="card">
                <div class="card-header text-center">
                    <h5>Select Date Range</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" id="dateForm">
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date (>= 2022-07-20)</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <a class="btn btn-danger" href="">Clear</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Loading Spinner -->
    <div class="row justify-content-center text-center mt-4" id="loadingSpinner" style="display:none;">
        <div class="text-center">
            <div class="spinner-border mt-1 text-primary" role="status">
                <span class="visually-hidden"></span>
            </div>
            <p>Loading data, please wait...</p>
        </div>
    </div>
    <?php if ($xmlData) : ?>
        <!-- Pivot Table Output -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-14">
                <h3 class="text-center"><?php echo $page_title; ?></h3>
                <p class="text-center">
                    <?php if ($startDate && $endDate) : ?>
                        Showing data from <strong><?php echo $startDate; ?></strong> to <strong><?php echo $endDate; ?></strong>
                    <?php endif; ?>
                </p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                        <tr>
                            <th>Institution</th>
                            <th>Request Status</th>
                            <th>Borrowing Request Status</th>
                            <th>Avg Hours Request Created to Sent Material</th>
                            <?php foreach ($distinctColumn3 as $col3Val) : ?>
                                <th><?php echo htmlspecialchars($col3Val); ?></th>
                            <?php endforeach; ?>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        // Calculate grand totals for pivot columns and overall pivot total
                        $grandTotals = array_fill_keys($distinctColumn3, 0.0);
                        $grandRowTotal = 0.0;
                        // Loop through each Lender Institution group (Column1)
                        foreach ($pivotData as $c1Val => $statuses) {
                            // Calculate total rows for this lender:
                            // For each Request Status group, rows = (data rows + 1 subtotal row)
                            // Then add 1 for the lender total row.
                            $lenderRowCount = -1;
                            foreach ($statuses as $status => $reqTypes) {
                                $lenderRowCount += count($reqTypes) + 1;
                            }
                            $lenderRowCount += 1;
                            
                            $firstC1 = true;
                            $groupTotals = array_fill_keys($distinctColumn3, 0.0);
                            $groupRowTotal = 0.0;
                            // Loop through each Request Status group within this lender
                            foreach ($statuses as $status => $reqTypes) {
                                $statusRowCount = count($reqTypes) + 1; // data rows + subtotal row
                                $firstStatus = true;
                                $statusTotals = array_fill_keys($distinctColumn3, 0.0);
                                $statusRowTotal = 0.0;
                                foreach ($reqTypes as $reqType => $data) {
                                    echo "<tr>";
                                    if ($firstC1) {
                                        echo "<td rowspan='{$lenderRowCount}'>" . htmlspecialchars($c1Val) . "</td>";
                                        $firstC1 = false;
                                    }

                                    if ($firstStatus) {
                                        echo "<td rowspan='" . ($statusRowCount - 1) . "'>" . htmlspecialchars($status) . "</td>";
                                        $firstStatus = false;
                                    }

                                    echo "<td>" . htmlspecialchars($reqType) . "</td>";
                                    $timeVal = isset($timeData[$c1Val][$status][$reqType]) ? $timeData[$c1Val][$status][$reqType] : 0;
                                    echo "<td>" . $timeVal . "</td>";
                                    $rowTotal = 0.0;
                                    foreach ($distinctColumn3 as $col3Val) {
                                        $cellVal = isset($data[$col3Val]) ? $data[$col3Val] : 0;
                                        echo "<td>" . $cellVal . "</td>";
                                        $rowTotal += $cellVal;
                                        $groupTotals[$col3Val] += $cellVal;
                                        $statusTotals[$col3Val] += $cellVal;
                                    }
                                    echo "<td class='subtotal-row'>" . $rowTotal . "</td>";
                                    echo "</tr>";
                                    $groupRowTotal += $rowTotal;
                                    $statusRowTotal += $rowTotal;
                                }
                                // Output Request Status subtotal row just above the lender total row.
                                echo "<tr class='subtotal-row'>";
                                // Instead of a blank cell in the Lender Institution column, we now output a blank cell to align totals with data rows.
                                // Then in the Request Status column output the subtotal text, then one blank cell for Request Type and one for Time.
                              //  echo "<td></td>"; 
                                echo "<td>Subtotal for " . htmlspecialchars($status) . "</td>";
                                echo "<td></td>";
                                echo "<td></td>";
                                foreach ($distinctColumn3 as $col3Val) {
                                    echo "<td>" . $statusTotals[$col3Val] . "</td>";
                                }
                                echo "<td>" . $statusRowTotal . "</td>";
                                echo "</tr>";
                            }
                            // Output Lender Institution total row
                            echo "<tr class='subtotal-row'>";
                            
                            echo "<td>Total for " . htmlspecialchars($c1Val) . "</td>";
                            echo "<td></td>";
                            echo "<td></td>";
                            echo "<td></td>";
                            foreach ($distinctColumn3 as $col3Val) {
                                echo "<td>" . $groupTotals[$col3Val] . "</td>";
                            }
                            echo "<td>" . $groupRowTotal . "</td>";
                            echo "</tr>";
                            $grandRowTotal += $groupRowTotal;
                            foreach ($distinctColumn3 as $col3Val) {
                                $grandTotals[$col3Val] += $groupTotals[$col3Val];
                            }
                        }
                        ?>
                        </tbody>
                        <tfoot>
                        <tr class="grandtotal-row">
                            <th colspan="4">Grand Total</th>
                            <?php foreach ($distinctColumn3 as $col3Val) : ?>
                                <th><?php echo $grandTotals[$col3Val]; ?></th>
                            <?php endforeach; ?>
                            <th><?php echo $grandRowTotal; ?></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <!-- Download link for raw CSV -->
                <?php if ($csvFileName) : ?>
                    <div class="text-center mt-3 mb-5">
                        <a href="<?php echo $csvFileName; ?>" class="btn btn-success">Download Raw CSV</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js"></script>
<script>
    document.getElementById('dateForm').onsubmit = function () {
        document.getElementById('loadingSpinner').style.display = 'block';
    };
    document.getElementById('loadingSpinner').style.display = 'none';
</script>
</body>
</html>