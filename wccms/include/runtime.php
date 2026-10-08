<?php
// UK civil time follows GMT/BST automatically; stored values are not rewritten.
date_default_timezone_set('Europe/London');
// Make database exceptions consistent across supported PHP versions.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
