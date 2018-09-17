<?php
    function processXML($xmlFileName) {
        //Include
        include("connect.php");

        //Init
        global $xmlDirectory;
        $TYP = "ST";
        $V1 = "IMG";
        $V2 = "";

        //Init - Get Username
        $sqlGetUser = "SELECT owner FROM rpi WHERE rpiID = {$_GET["rpid"]}";
        $resultsGetUser = mysqli_query($conn, $sqlGetUser);
        $USR = mysqli_fetch_assoc($resultsGetUser)['owner'];

        //Get Timestamp    
        date_default_timezone_set('UTC');
        $dateFormat = "A d B I-M-Sp";
        $dateObj = date_create_from_format($dateFormat, $xmlFileName);
        $TS = date("Y-m-d H:i:s", getTimestamp());

        //Update DB
        $sqlInsertIntoLog = "INSERT INTO log (VID, TYP, USR, RPID, V1, V2, TS) VALUES (NULL, '{$TYP}', '{$USR}', '{$RPID}', '{$V1}', '{$V2}', '{$TS}');";
        $resultInsertIntoLog = mysqli_query($conn, $sqlInsertIntoLog);
    }

    //TODO: Filter $_GET
    $xmlDirectory = "../../DetectDir/";
    $xmlFilename = $_GET["capture"];
    $xmlFullFilePath = $xmlDirectory . $xmlFilename;

    if(is_dir($xmlFullFilePath)) {
        try {
            processXML($xmlFilename);
            echo "OK";
        } catch (Exception $e) { echo "ERROR"; }
    } else { echo "NO"; }
?>