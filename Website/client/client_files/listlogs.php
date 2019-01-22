<?php
    //Require
    require("../../index_files/sessionstart.php");
    require("../../index_files/sessioncheck.php");
    require("../../index_files/connect.php");
    require("../../index_files/operations.php");

    //Database Queries
    $currentUser = $_SESSION["username"]; //Current User
    $sqlCurrentStatus = "SELECT VN, VV, TS, RPID FROM status NATURAL JOIN vitals WHERE USR='{$currentUser}';"; //Select all current status related to the current user
    $sqlLog = "SELECT V.VN, TYP, l.RPID, V1, V2, TS FROM log AS l NATURAL JOIN vitals AS V WHERE l.USR='{$currentUser}' ORDER BY l.RPID, l.TS DESC;"; //Select all logs related to the current user

    //Execute Queries
    $resultCurrentStatus = mysqli_query($conn, $sqlCurrentStatus);
    $resultLog = mysqli_query($conn, $sqlLog);

    //Store CurrentStatus Query Results into $arrayCurrentStatus
    $arrayCurrentStatus = array(); 
    if(mysqli_num_rows($resultCurrentStatus) > 0) {
        while($row = mysqli_fetch_assoc($resultCurrentStatus)) {
            $tempRow = "";
            $tempVV = "";
            switch ($row['VN']) {
                case 'BatteryVoltage':
                case 'SolarPanelVoltage':
                    $tempVV = round($row['VV'], 2) . "V";
                    break;
                case 'BatteryCurrent':
                case 'SolarPanelCurrent':
                case 'ChargeControllerCurrent':
                    $tempVV = round($row['VV'], 2) . "A";
                    break;
                case 'TemperatureInner':
                case 'TemperatureOuter':
                    $tempVV = round($row['VV'], 2) . "&deg;C";
                    break;
                case 'HumidityInner':
                case 'HumidityOuter':
                    $tempVV = round($row['VV'], 2) . "g/m<sup>3</sup>";
                    break;
                // case 'GPS':
                //     $tempOne = (round($row['VV'], 2) > 0) ? round($row['VV'], 2) . '&deg;N' : round($row['VV'], 2) . '&deg;S';
                //     $tempTwo = (round($row['VV'], 2) > 0) ? round($row['VV'], 2) . '&deg;E' : round($row['VV'], 2) . '&deg;W';
                //     $tempVV = $tempOne . '&comma;' . $tempTwo; 
                //     break;
                default:
                    $tempVV = $row['VV'];
                    break;
            }
            $tempRow = "[ {$row['VN']}, {$tempVV}, {$row['RPID']}, {$row['TS']} ]";
            $arrayCurrentStatus[] = $tempRow;
        }
    }

    //Store Log Query Results into $arrayLog
    $arrayLog = array();
    if(mysqli_num_rows($resultLog) > 0) { 
        while($row = mysqli_fetch_assoc($resultLog)) {
            $tempRow = "";
            $tempVOne = "";
            switch ($row['VN']) {
                case 'BatteryVoltage':
                case 'SolarPanelVoltage':
                    $tempVOne = round($row['V1'], 2) . "V";
                    break;
                case 'BatteryCurrent':
                case 'SolarPanelCurrent':
                case 'ChargeControllerCurrent':
                    $tempVOne = round($row['V1'], 2) . "A";
                    break;
                case 'TemperatureInner':
                case 'TemperatureOuter':
                    $tempVOne = round($row['V1'], 2) . "&deg;C";
                    break;
                case 'HumidityInner':
                case 'HumidityOuter':
                    $tempVOne = round($row['V1'], 2) . "g/m<sup>3</sup>";
                    break;
                case 'GPS':
                    $tempOne = (round($row['V1'], 2) > 0) ? round($row['V1'], 2) . '&deg;N' : round($row['V1'], 2) . '&deg;S';
                    $tempTwo = (round($row['V2'], 2) > 0) ? round($row['V2'], 2) . '&deg;E' : round($row['V2'], 2) . '&deg;W';
                    $tempVOne = $tempOne . '&comma;' . $tempTwo; 
                    break;
                default:
                    $tempVOne = $row['V1'];
                    break;
            }
            $tempRow = "[ {$row['VN']}, {$row['TYP']}, {$row['RPID']}, {$tempVOne}, {$row['TS']} ]";
            $arrayLog[] = $tempRow;
        }
    }

    //Functions
    $initalRaspberryPi = true; //Initialize
    $currentRaspberryPi = "-1"; //Initialize

    //getData - Generate and format HTML tables to display CurrentStatus and Log results, respectively
    function getData($stringToReplace, $tableHeader, $printAll) { 
        $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
        $displayHTML = ""; //Initialize Display HTML
        global $initalRaspberryPi;
        global $currentRaspberryPi;
        
        if ($stringSplit[2] !== $currentRaspberryPi) { //$stringSplit[2] will always be the RaspberryPi ID - Conditional will create the new table header and caption for the respective RaspberryPi
            $currentRaspberryPi = $stringSplit[2];

            if($initalRaspberryPi === false){ $displayHTML .= "</table>"; } //Closes the table from the previous RaspberryPi
            else { $initalRaspberryPi = false; } //Initial table will change this to false after it creates the first table header

            $displayHTML .= "<table>{$tableHeader}";
        }

        //Generate HTML currentstatus/log tables
        $displayHTML .= "<tr>";
        for ($i = 0; $i < count($stringSplit); $i++) { 
            if($i === 2 && !$printAll) { continue; }
            $displayHTML .= "<td>{$stringSplit[$i]}</td>"; 
        }
        $displayHTML .= "</tr>";

        //Return table(s)
        return $displayHTML;
    }

    //getTimeStamp - Gets the timestamp of the current status to display it on the HTML page
    function getTimeStamp($stringToReplace) { 
        $stringSplit = splitDataIntoArray($stringToReplace); //Remove extra characters and place data into array
        return end($stringSplit); //Return timestamp for currentstatus fieldset
    }

    //Reset Globals
    function resetGlobals() {
        global $initalRaspberryPi;
        global $currentRaspberryPi;

        $initalRaspberryPi = true; //Reset currentRaspberryPi 'Counter'
        $currentRaspberryPi = "-1"; //Reset currentRaspberryPi 'Counter'
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="listlogs.css">
    </head>

    <body>            
        <div class="displays">
            <fieldset><legend>Current Status</legend>
                <?php
                    foreach ($arrayCurrentStatus as $aCS) { echo getData($aCS, "<tr><th>Vital Name</th><th>Status</th><th>Timestamp</th></tr>", false); }
                    echo "</table>";
                    resetGlobals();
                ?>
            </fieldset>
        </div>
        <div class="displays">
            <fieldset><legend>Log</legend>
                <?php
                    foreach ($arrayLog as $aL) { echo getData($aL, "<tr><th>Vital Name</th><th>TYP</th><th>RPiID</th><th>Vital Value</th><th>Timestamp</th></tr>", true); }
                    echo "</table>";
                    resetGlobals();
                ?>
            </fieldset>
        </div>
    </body>
</html>