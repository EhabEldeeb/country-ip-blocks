<?php
$WebInterface = true;
if (isset($_GET['api'])) {
    $WebInterface = false;
    header('Content-Type: application/json');
    $response = [];
} else {
    ?>
    <form method="POST">
        <input type="text" name="ip" placeholder="IP" />
        <button type="submit">Find Country</button>
    </form>
    <?php
}
if (isset($_REQUEST['ip'])) {
    $ip = $_REQUEST['ip'];
    $found = false;
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        $msg = "Invalid IP Address.";
        $response = ["status" => "error", "status_code" => 1, "response" => $msg];
        if ($WebInterface)
            echo $msg;
        $found = true;
    }
    if (!$found) {
        $file = file_get_contents("ipv4.csv");
        $data = explode(PHP_EOL, $file);
        $localRanges = [
            "10.0.0.0", "10.255.255.255", // Class A
            "172.16.0.0", "172.31.255.255", // Class B
            "192.168.0.0", "192.168.255.255" // Class C
        ];
        $carrierGradeRanges = [
            "100.64.0.0", "100.127.255.255" // In April 2012, IANA allocated the 100.64.0.0/10 block of IPv4 addresses specifically for use in carrier-grade NAT scenarios.
        ];
        foreach ($data as $d) {
            $d = explode(",", $d);
            $ipAddr = ip2long($ip);
            if (count($d) <= 1) {
                break;
            } else if (($ipAddr > ip2long($localRanges[0]) && $ipAddr <= ip2long($localRanges[1]))) {
                $msg = "This is a local IP. it only exists in a private network (Class A).";
                $response = ["status" => "success", "status_code" => 0, "response" => $msg];
                if ($WebInterface)
                    echo $msg;
                $found = true;
                break;
            } else if (($ipAddr > ip2long($localRanges[2]) && $ipAddr <= ip2long($localRanges[3]))) {
                $msg = "This is a local IP. it only exists in a private network (Class B).";
                $response = ["status" => "success", "status_code" => 0, "response" => $msg];
                if ($WebInterface)
                    echo $msg;
                $found = true;
                break;
            } else if (($ipAddr > ip2long($localRanges[4]) && $ipAddr <= ip2long($localRanges[5]))) {
                $msg = "This is a local IP. it only exists in a private network (Class C).";
                $response = ["status" => "success", "status_code" => 0, "response" => $msg];
                if ($WebInterface)
                    echo $msg;
                $found = true;
                break;
            } else if ($ipAddr > ip2long($carrierGradeRanges[0]) && $ipAddr <= ip2long($carrierGradeRanges[1])) {
                $msg = "This is a Carrier Grade NAT Address.";
                $response = ["status" => "success", "status_code" => 0, "response" => $msg];
                if ($WebInterface)
                    echo $msg;
                $found = true;
                break;
            } else if ($ipAddr >= ip2long($d[3]) && $ipAddr <= ip2long($d[4])) {
                $resp['country_code'] = strtoupper($d[0]);
                $resp['country_name'] = $d[1];
                $response = ["status" => "success", "status_code" => 0, "response" => $resp];
                if ($WebInterface)
                    echo "[" . $resp['country_code'] . "] ==> " . $resp['country_name'];
                $found = true;
                break;
            }
        }
    }
    if (!$found) {
        $msg = "Can't find country for this IP.";
        $response = ["status" => "error", "status_code" => 2, "response" => $msg];
        if ($WebInterface)
            echo $msg;
    }
} else {
    $msg = "IP Address not supplied.";
    $response = ["status" => "error", "status_code" => 3, "response" => $msg];
    if ($WebInterface)
        echo $msg;
}

if (!$WebInterface) {
    $response['IP Address'] = $_REQUEST['ip'] ?? "";
    echo json_encode($response);
}