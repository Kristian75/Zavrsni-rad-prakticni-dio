<?php
$host = '127.0.0.1';
$dbname = 'knjiga_inventara_csv';
$username = 'root';
$password = '';
header('Content-Type: application/json');

try {

    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    $draw = isset($_POST['draw']) ? intval($_POST['draw']) : 1;
    $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
    $length = isset($_POST['length']) ? intval($_POST['length']) : 10;
    $searchTerm = isset($_POST['searchTerm']) ? $_POST['searchTerm'] : '';

    $query = "SELECT `PREZIME I IME AUTORA`, `NASLOV_mjesto_godina`, `SIGNATURA`, `DOBAVLJAČ`, `DATUM` 
              FROM knjiga_inventara_csv WHERE 1=1";
    
    $totalRecordsQuery = $conn->query("SELECT COUNT(*) FROM knjiga_inventara_csv");
    $totalRecords = $totalRecordsQuery->fetchColumn();

    if (!empty($searchTerm)) {
        $query .= " AND (`PREZIME I IME AUTORA` LIKE :searchTerm)";
    }

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

    $query .= " LIMIT :start, :length";
    
    $stmt = $conn->prepare($query);
    
    if (!empty($searchTerm)) {
        $stmt->bindValue(':searchTerm', '%' . $searchTerm . '%');
    }

    $stmt->bindValue(':start', $start, PDO::PARAM_INT);
    $stmt->bindValue(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
   
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
        'trace' => $e->getTrace()
    ]);
}
?>