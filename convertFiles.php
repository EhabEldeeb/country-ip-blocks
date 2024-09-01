<?php
function ipRange($cidr, $ipVersion = 4) {
    if ($ipVersion == 4) { // https://stackoverflow.com/a/55229198
        $range = array();
        $cidr = explode('/', $cidr);
        $range[0] = long2ip((ip2long($cidr[0])) & ((-1 << (32 - (int) $cidr[1]))));
        $range[1] = long2ip((ip2long($range[0])) + pow(2, (32 - (int) $cidr[1])) - 1);
        return $range;
    } else if ($ipVersion == 6) { // modified version of this snippet https://stackoverflow.com/a/61883041
        list($addr_given_str, $cidrlength) = explode('/', $cidr);
        $addr_given_bin = inet_pton($addr_given_str);
        $addr_given_hex = bin2hex($addr_given_bin);
        $addr_given_str = inet_ntop($addr_given_bin);
        $flexbits = 128 - $cidrlength;
        $addr_hex_first = $addr_given_hex;
        $addr_hex_last = $addr_given_hex;
        $pos = 31;
        while ($flexbits > 0) {
            $orig_first = substr($addr_hex_first, $pos, 1);
            $orig_last = substr($addr_hex_last, $pos, 1);
            $origval_first = hexdec($orig_first);
            $origval_last = hexdec($orig_last);
            $mask = 0xf << (min(4, $flexbits));
            $new_val_first = $origval_first & $mask;
            $new_val_last = $origval_last | (pow(2, min(4, $flexbits)) - 1);
            $new_first = dechex($new_val_first);
            $new_last = dechex($new_val_last);
            $addr_hex_first = substr_replace($addr_hex_first, $new_first, $pos, 1);
            $addr_hex_last = substr_replace($addr_hex_last, $new_last, $pos, 1);
            $flexbits -= 4;
            $pos -= 1;
        }
        $addr_bin_first = hex2bin($addr_hex_first);
        $addr_bin_last = hex2bin($addr_hex_last);
        $addr_str_first = inet_ntop($addr_bin_first);
        $addr_str_last = inet_ntop($addr_bin_last);
        return [$addr_str_first, $addr_str_last];
    } else {
        return [null, null];
    }
}
function getCountryData() {
    $countryFile = file_get_contents("countries.csv");
    $d = explode(PHP_EOL, $countryFile);
    $countryData = [];
    foreach ($d as $c) {
        $cn = explode(",", $c);
        $countryData[strtolower($cn[0])] = $cn[1] ?? "UNKNOWN";
    }
    return $countryData;
}
function processRawFiles($folder) {
    $start = microtime(true);
    $countryData = getCountryData();
    $files = array_diff(scandir($folder), [".", ".."]);
    $writefile = fopen("$folder.csv", "w");
    fwrite($writefile, "country_2,country,block,range_from,range_to" . PHP_EOL);
    fclose($writefile);
    $writefile = fopen("$folder.csv", "a");
    $i = 1;
    foreach ($files as $k) {
        $data = file_get_contents("$folder/$k");
        $filename = substr($k, 0, 2);
        $datalines = explode(PHP_EOL, $data);
        foreach ($datalines as $l) {
            if ($l == "")
                continue;
            if ($folder == "ipv4")
                $range = ipRange($l, 4);
            else if ($folder == "ipv6")
                $range = ipRange($l, 6);
            $writeline = $filename . "," . ($countryData[$filename] ?? "UNKNOWN") . "," . $l . "," . ($range[0] ?? "") . "," . ($range[1] ?? "") . PHP_EOL;
            fwrite($writefile, $writeline);
            $timenow = number_format(microtime(true) - $start, 2);
            echo "Processed $i Lines in $timenow sec.\n";
            $i++;
        }
    }
    fclose($writefile);
    return number_format(microtime(true) - $start, 2);
}

function getCountryNames($file) {
    $data = file_get_contents($file);
    $d = explode(PHP_EOL, $data);
    $countryData = [];
    foreach ($d as $c) {
        $cn = explode(",", $c);
        $countryData[strtolower($cn[0])] = $cn[1];
    }
    return $countryData;
}

$ipv4 = processRawFiles("ipv4");
$ipv6 = processRawFiles("ipv6");

echo "IPv4 took " . $ipv4 . " seconds" . PHP_EOL;
echo "IPv6 took " . $ipv6 . " seconds" . PHP_EOL;