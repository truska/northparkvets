<?php

    function fetchUniqueSections() {

        $uniqueSections = [];
      //  $query = "SELECT DISTINCT `section` FROM `cms_dashboard` WHERE `showonweb` = 'Yes' AND `archived` = 0 ORDER BY `section` ASC";
        $query = "
            SELECT DISTINCT 
                d.`section`
            FROM 
                `cms_dashboard` d
            JOIN 
                `cms_dashboard_sections` s ON d.`section` = s.`id`
            WHERE 
                d.`showonweb` = 'Yes' 
                AND d.`archived` = 0 
                AND s.`showonweb` = 'Yes'
            ORDER BY 
                d.`section` ASC";

        $result = DB::query($query);

        if ($result) {

            while ($row = mysqli_fetch_assoc($result)) {

                $uniqueSections[] = $row['section'];

            }

        }

        return $uniqueSections;

    }


    function fetchSectionNames() {

    $sectionNames = [];

    $query = "SELECT `id`, `name` FROM `cms_dashboard_sections` WHERE `showonweb` = 'Yes' AND `archived` = 0 ORDER BY `id` ASC";

    $result = DB::query($query);

    if ($result) {

        while ($row = mysqli_fetch_assoc($result)) {

            $sectionNames[$row['id']] = $row['name'];

        }

    }

    return $sectionNames;

    }


    $sectionNames = fetchSectionNames();

    function getClassIdBasedOnDate() {
        $sqlNextRide = "SELECT `id` FROM `onlineEntries_classes` WHERE `eventID` > '21' AND `classDate` > NOW() ORDER BY `classDate` ASC LIMIT 1";
        $result = DB::query($sqlNextRide);
        if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['id'];
        } else {
        return null; // Or handle this case as needed
        }
    }

    function fetchCountsFromDatabase($section) {

        if (!is_scalar($section)) {
            // Log unexpected input and return
            error_log('fetchCountsFromDatabase called with non-scalar section: ' . print_r($section, true));
            return []; // Return empty array or handle this scenario as appropriate
        }
        //error_log("Sections: " . $section);


        // Get the next ride ID 
        // Part of EventPal custom - to be rewritten and un commented 
        // also see function
        //$nextRideID = getClassIdBasedOnDate();


        $countsData = [];

        // Include `method` in the SELECT query
       // $query = "SELECT `name`, `title`, `colour`, `icon`, `symbol`, `sqlcode`, `method` FROM `cms_dashboard` WHERE `section` = {$section} AND `showonweb` = 'Yes' AND `archived` = 0 ORDER BY `sort` ASC";

        // Checking section is to be visible
        $query = "
            SELECT 
                d.`name`, 
                d.`title`, 
                d.`colour`, 
                d.`icon`, 
                d.`symbol`, 
                d.`sqlcode`, 
                d.`method` 
            FROM 
                `cms_dashboard` d
            JOIN 
                `cms_dashboard_sections` s ON d.`section` = s.`id` 
            WHERE 
                d.`section` = {$section} 
                AND d.`showonweb` = 'Yes' 
                AND d.`archived` = 0 
                AND s.`showonweb` = 'Yes' 
            ORDER BY 
                d.`sort` ASC";

        $result = DB::query($query);

        if ($result) {

            while ($row = mysqli_fetch_assoc($result)) {

                // Replace placeholder in sqlcode with actual nextRideID
                $sqlcodeWithRideID = str_replace("{nextRideID}",$nextRideID, $row['sqlcode']);

                // Example of logging the final query
                // error_log("Executing  WithRideID SQL: " . $sqlcodeWithRideID);

                if ($row['method'] == 'count') {
                // error_log("Executing  WithRideID Count SQL: " . $sqlcodeWithRideID);

                    // The original count logic
                    $countResult = DB::query($sqlcodeWithRideID);
                    $number = mysqli_num_rows($countResult);
                } elseif ($row['method'] == 'value') {

                    //error_log("Executing  WithRideID Value SQL: " . $sqlcodeWithRideID);
                    // New logic to handle fetching a fixed value
                    // This might involve executing $sqlcodeWithRideID if it contains a query to fetch the value
                    // or simply parsing $sqlcodeWithRideID as the value directly if no query is needed
                    $valueResult = DB::query($sqlcodeWithRideID);
                    if ($valueResult && $valueRow = mysqli_fetch_assoc($valueResult)) {
                        // Assuming the value you want to fetch is in a field named 'fixed_value'
                        $number = $valueRow['fixed_value'];
                    } else {
                        // Handle cases where the value cannot be fetched
                        $number = 0; // Or any default/fallback value
                    }
                } else {
                    // Fallback in case method is not recognized
                    $number = 0;
                }

                $countData = [
                    'number' => $number, // This now either represents a count or a fixed value
                    'name'   => $row['title'],
                    'colour' => $row['colour'],
                    'symbol' => $row['symbol'],
                ];

                $countsData[] = $countData;
            }
        }

        return $countsData;
    }


    $sections = fetchUniqueSections();


    $allCountsDataBySection = [];

    foreach ($sections as $section) {

        $allCountsDataBySection[$section] = fetchCountsFromDatabase($section);

    }