<?php declare(strict_types=1);

require_once __DIR__ . "/../../vendor/autoload.php";

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\PHP as PHPReport;

$filter = new Filter();
$filter->includeDirectory(__DIR__ . "/../../app");
$filter->excludeDirectory(__DIR__ . "/../../vendor");

$coverage = new CodeCoverage(
    (new Selector())->forLineCoverage($filter),
    $filter,
);

$testName = $_SERVER["HTTP_TESTNAME"];
$testIdx = $_SERVER["HTTP_TESTASSERTINDEX"];

$coverage->start($testName . "_" . $testIdx);

require_once __DIR__ . "/index.real.php";

$coverage->stop();

(new PHPReport())->process(
    $coverage,
    __DIR__ .
        "/../../test/tools/raw_coverage/" .
        $testName .
        "_" .
        $testIdx .
        ".cov",
);
