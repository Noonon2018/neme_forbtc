<?php
echo "<h3>Testing Internet Connectivity...</h3>";

// Set timeout to 5 seconds
$context = stream_context_create(['http' => ['timeout' => 5]]);
$google_homepage = @file_get_contents('https://www.google.com', false, $context);

if ($google_homepage === FALSE) {
    echo "<p style='color:red; font-weight:bold;'>CONNECTION FAILED.</p>";
    echo "<p>Your local PHP server cannot reach the internet. This is most likely caused by a firewall blocking Apache/PHP, or a local network configuration issue.</p>";
} else {
    echo "<p style='color:green; font-weight:bold;'>CONNECTION SUCCESSFUL!</p>";
    echo "<p>Your local PHP server can reach the internet. The issue might be specific to connecting to the CoinGecko API.</p>";
}
?> 