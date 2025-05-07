<?php
// Database configuration
$host = '127.0.0.1'; // Using 127.0.0.1 instead of localhost for better reliability
$dbname = 'knjiga_inventara_csv';
$username = 'root';
$password = '';
header('Content-Type: application/json');

try {

    // Database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    // Get the DataTables parameters
    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $searchTerm = isset($_POST['searchTerm']) ? $_POST['searchTerm'] : '';

    // Base query
    $query = "SELECT `PREZIME I IME AUTORA`, `NASLOV_mjesto_godina`, `SIGNATURA`, `DOBAVLJAČ`, `DATUM` 
              FROM knjiga_inventara_csv WHERE 1=1";
    
    // Total records count (without filtering)
    $totalRecordsQuery = $conn->query("SELECT COUNT(*) FROM knjiga_inventara_csv");
    $totalRecords = $totalRecordsQuery->fetchColumn();

    // Add search condition if search term exists
    if (!empty($searchTerm)) {
        $query .= " AND (`PREZIME I IME AUTORA` LIKE :searchTerm)";
    }
    // print_r($searchTerm);die;
    // Filtered records count
    $filteredQuery = $conn->prepare(str_replace(
        '`PREZIME I IME AUTORA`, `NASLOV_mjesto_godina`, `SIGNATURA`, `DOBAVLJAČ`, `DATUM`', 
        'COUNT(*)', 
        $query
    ));
    
    if (!empty($searchTerm)) {
        $filteredQuery->bindValue(':searchTerm', '%' . $searchTerm . '%');
    }
    $filteredQuery->execute();
    $filteredRecords = $filteredQuery->fetchColumn();

    // Add pagination to the query
    $query .= " LIMIT :start, :length";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
    }

    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
   
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // Prepare the response
    $response = [
        "draw" => $draw,
        "recordsTotal" => $totalRecords,
        "recordsFiltered" => $filteredRecords,
        "data" => $books
    ];
    
    echo json_encode($response);
    
} catch(PDOException $e) {
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTrace() // Only for debugging, remove in production
    ]);
}
?>