<?php
/**
 * Reports Index
 * php version 8.1
 *
 * @category Alma
 * @package  WRLC
 * @author   Tom Boone <tom.boone@wrlc.org>
 * @license  https://opensource.org/licenses/gpl-license.php GNU Public License
 * @link     https://github.com/WRLC/interactive-alma-reports/public/index.php Repository File
 */

/**
 * Get the string between two strings
 *
 * @param string $str   String to parse
 * @param string $start Start string
 * @param string $end   End string
 *
 * @return string
 */
function stringBetweenTwoStrings(string $str, string $start, string $end): string
{
    $substringStart = strpos($str, $start);
    $substringStart += strlen($start);
    $size = strpos($str, $end, $substringStart) - $substringStart;
    return substr($str, $substringStart, $size);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alma Interactive Reports</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row mt-3">
            <div class="col-md-8 offset-md-2">
                <div class="card bg-light">
                    <div class="card-header bg-primary text-white text-center">
                        <h4>Alma Interactive Reports</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <?php
                            $files = scandir(__DIR__);
                            $links = [];
                            foreach ($files as $file) {
                                if (str_contains($file, '.php') && $file != 'index.php') {
                                    $doc = token_get_all(file_get_contents($file))[2][1];
                                    $title = stringBetweenTwoStrings($doc, '/** * ', ' * php version');
                                    $links[$title] = $file;
                                }
                            }
                            ksort($links);
                            foreach ($links as $title => $link) {
                                ?>

                                <a href='<?php echo $link ?>' class='list-group-item list-group-item-action'><?php echo $title ?></a>
                                <?php
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies (Popper.js and jQuery) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>