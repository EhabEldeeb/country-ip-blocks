<form method="POST">
    <input type="text" name="IP" placeholder="IP" />
    <button type="submit">Find Country</button>
</form>
<?php
if (isset($_POST['IP'])) {
    $ip = $_POST['IP'];
    $found = false;
    if (!filter_var($ip, FILTER_VALIDATE_IP)) {
        echo "Invalid IP Address.";
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
                echo "This is a local IP. it only exists in a private network (Class A).";
                $found = true;
                break;
            } else if (($ipAddr > ip2long($localRanges[2]) && $ipAddr <= ip2long($localRanges[3]))) {
                echo "This is a local IP. it only exists in a private network (Class B).";
                $found = true;
                break;
            } else if (($ipAddr > ip2long($localRanges[4]) && $ipAddr <= ip2long($localRanges[5]))) {
                echo "This is a local IP. it only exists in a private network (Class C).";
                $found = true;
                break;
            } else if ($ipAddr > ip2long($carrierGradeRanges[0]) && $ipAddr <= ip2long($carrierGradeRanges[1])) {
                echo "This is a Carrier Grade NAT Address.";
                $found = true;
                break;
            } else if ($ipAddr >= ip2long($d[3]) && $ipAddr <= ip2long($d[4])) {
                echo "[" . strtoupper($d[0]) . "] ==> " . $d[1];
                $found = true;
                break;
            }
        }
    }
    if (!$found)
        echo "Can't find country for this IP.";
}