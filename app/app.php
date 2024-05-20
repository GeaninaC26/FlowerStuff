<?php
session_start();

function query_db($query, $params){
    $conn = new PDO("sqlite:flower_catalogue.db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare($query);
    
    foreach ($params as $key => &$value) {
        $stmt->bindParam($key, $value, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function process_route($route){
    if(preg_match("/^\/register$/", $route)){
        include 'register.html';            
        die();
    }
    if(isset($_SESSION['user'])){
        if($_SESSION['user']['nume'] == 'admin'){
            include 'admin.html';
            die();
        }
        if(preg_match("/^\/$/", $route)){
            include 'home.html';
        }
        if(preg_match("/^\/catalog$/", $route)){
            include 'catalog.html';
        }
        if(preg_match("/^\/tips$/", $route)){
            include 'tips.html';
        }
        if(preg_match("/^\/care$/", $route)){
            include 'care.html';
        }
    }else{
        include 'login.html';
    }

    die();
}

function process_request(){
    $method = $_SERVER['REQUEST_METHOD'];
    $endpoint = strtok($_SERVER['REQUEST_URI'], '?'); // Extract the endpoint without query parameters

    if (preg_match("/^(\/|\/register|\/catalog|\/tips|\/care)$/", $endpoint)) {
        process_route($endpoint);
    }

    header('Content-Type: application/json');

    switch ($method) {
        case 'GET':
            switch ($endpoint) {
                case '/api/flower':
                    $flowers = query_db('SELECT * FROM floare', []);
                    echo json_encode($flowers);
                    break;
                case '/api/county':
                    $counties = query_db('SELECT * FROM judet', []);
                    echo json_encode($counties);
                    break;
                case '/api/tips':
                    $tips = query_db('SELECT * FROM tips', []);
                    echo json_encode($tips);
                    break;
                case '/api/cities':
                    if (isset($_GET['search'])) {
                        $searchTerm = $_GET['search'];
                        $cities = query_db('SELECT * FROM oras WHERE nume LIKE :searchTerm', [':searchTerm' => '%' . $searchTerm . '%']);
                    } else {
                        $cities = query_db('SELECT * FROM oras', []);
                    }
                    echo json_encode($cities);
                    break;
                case '/api/users':
                    // Check if there's a search term
                    if (isset($_GET['search'])) {
                        $searchTerm = $_GET['search'];
                        // Perform search based on the search term
                        $users = query_db('SELECT user.nume, user.email, user.phone, user.address, oras.nume AS city_name, judet.nume AS county_name FROM user INNER JOIN oras ON user.id_oras = oras.id INNER JOIN judet ON user.id_judet = judet.id WHERE user.nume LIKE :searchTerm', [':searchTerm' => '%' . $searchTerm . '%']);
                        echo json_encode($users);
                    } else {
                        // If no search term provided, return all users
                        $users = query_db('SELECT user.nume, user.email, user.phone, user.address, oras.nume AS city_name, judet.nume AS county_name FROM user INNER JOIN oras ON user.id_oras = oras.id INNER JOIN judet ON user.id_judet = judet.id', []);
                        echo json_encode($users);
                    }
                    break;
                default:
                    echo json_encode("Invalid endpoint");
            }
            break;
        case 'POST':
            switch ($endpoint) {
                case '/api/users':
                    $data = json_decode(file_get_contents('php://input'));
                    $params = [
                        ':nume' => $data->nume,
                        ':id_judet' => $data->id_judet,
                        ':id_oras' => $data->id_oras,
                        ':address' => $data->address,
                        ':email' => $data->email,
                        ':phone' => $data->phone,
                        ':password' => $data->password_hash
                    ];
                    try {
                        $result = query_db('INSERT INTO user (nume, id_judet, id_oras, address, email, phone, password_hash) VALUES (:nume, :id_judet, :id_oras, :address, :email, :phone, :password)', $params);
                        echo json_encode(['success' => $result]);
                     } catch (Exception $e) {
                        echo json_encode(['error' => $e->getMessage()]);
                     }
                    break;
                case '/api/login':
                    $data = json_decode(file_get_contents('php://input'));
                    $params = [
                        ':email' => $data->email,
                        ':password' => $data->password_hash
                    ];
                    $result = query_db('SELECT nume FROM user WHERE email = :email AND password_hash = :password', $params);
                    if (count($result) > 0) {
                        $_SESSION['user'] = $result[0];
                        echo json_encode(['success' => true]);
                    } else {
                        echo json_encode(['error' => 'Invalid credentials']);
                    }
                    break;
                case '/api/logout':
                    unset($_SESSION['user']);
                    echo json_encode(['success' => true]);
                    break;
                default:
                    echo json_encode("Invalid endpoint");
            }
            break;
        default:
            echo json_encode("Invalid request method");
    }
}

process_request();

?>
