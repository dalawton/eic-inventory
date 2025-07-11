<?php
// Creates connection to the host, references the .env file to add additional security to the server
// If any of the login information for the server changes, update in .env file.
require_once __DIR__ . '/vendor/autoload.php';      // This acts as a bridge from this file to the .env file to get the information stored in the .env file.
use Dotenv\Dotenv;

// Load environment variables (from .env)
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection parameters
    // This stores all the server information in variables which are local to this specific file
$serverName = $_ENV['DB_HOST'];
$dbUser = $_ENV['DB_USER'];
$databaseName = $_ENV['DB_DATABASE'];
$dbPassword = $_ENV['DB_PASSWORD'];

// This establishes the login information as combined
$connectionOptions = [
    "Database" => (string)$databaseName,
    "Uid" => (string)$dbUser,
    "PWD" => (string)$dbPassword,
    "Encrypt" => false,
    "TrustServerCertificate" => true,
];

// Connect to the sql server using the server name and the combined login data
$conn = sqlsrv_connect($serverName, $connectionOptions);

// throws an error if the connection cannot be established
if ($conn === false) {
    die("Connection failed: " . print_r(sqlsrv_errors(), true));
}
$partNumbers = $_POST['partNumber'] ?? [];
$descriptions = $_POST['description'] ?? [];
$amountsUsed = $_POST['amountUsed'] ?? [];

foreach ($partNumbers as $i => $pn) {
    $desc = $descriptions[$i] ?? '';
    $amt = $amountsUsed[$i] ?? 0;
    // Process as needed, e.g., update inventory, log usage, etc.
    $invCheck = sqlsrv_query($conn, "SELECT Amount from dbo.Inventory WHERE PN = ?", [$pn]);
    if ($row = sqlsrv_fetch_array($invCheck, SQLSRV_FETCH_ASSOC)) {
        $newQty = $row['Amount'] - $amt;
        sqlsrv_query($conn, "UPDATE dbo.Inventory SET Amount = ? WHERE PN = ?", [$newQty, $pn]);
    } else {
        sqlsrv_query($conn, "INSERT INTO dbo.Inventory (PN, Amount) VALUES (?, ?)", [$pn, 0]);
    }
}

echo "Parts processed and inventory updated!";
?>