<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permitir acceso desde cualquier origen
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include 'db.php';
include 'Peliculas.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("HTTP/1.1 200 OK");
    exit();
}

$method=$_SERVER['REQUEST_METHOD'];

switch($method){
    case 'GET':
	    handleGet($conn);
	    break;
    case 'POST':
	    handlePost($conn);
	    break;
    case 'PUT':
	    handlePut($conn);
	    break;
    case 'DELETE':
	    handleDelete($conn);
	    break;
    default:
	    http_response_code(405); 
	    echo json_encode(['message'=>'Método no permitido']);
	    break;
    }

function handleGet($conn){
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0; 
    
    if ($id > 0) {
        $smtp = $conn->prepare("SELECT * FROM peliculas WHERE ID=?");
        $smtp->execute([$id]);
        $peliculas = $smtp->fetch(PDO::FETCH_ASSOC);
        
        if ($peliculas) {
            $peliculaObjs = Peliculas::fromArray($peliculas);
            echo json_encode($peliculaObjs->toArray());
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Ese Id no existe']);
        } 
    } else {
        $smtp = $conn->prepare("SELECT * FROM peliculas");
        $smtp->execute();
        $peliculas = $smtp->fetchAll(PDO::FETCH_ASSOC);
        
        $peliculaObjs = array_map(fn($pelicula) => Peliculas::fromArray($pelicula)->toArray(), $peliculas);
        echo json_encode(['peliculas' => $peliculaObjs]);
    }
}


function handlePost($conn){
	
	$data= json_decode(file_get_contents('php://input'),true);
	$requiredFields=['titulo','genero','fecha_lanzamiento','duracion','director','reparto','sinopsis'];
	
	foreach($requiredFields as $field){
		if(!isset($data[$field])){
			http_response_code(400);
			echo json_encode(['message'=>'Ese Id no existe']);
			return; 
		}
	}

	$pelicula=Peliculas::fromArray($data);
	try{
		$smtp=$conn->prepare("INSERT INTO peliculas (titulo, genero, fecha_lanzamiento, duracion, director, reparto, sinopsis) VALUES (?,?,?,?,?,?,?)");
		$smtp->execute([
			$pelicula->titulo,
            $pelicula->genero,
			$pelicula->fecha_lanzamiento,
			$pelicula->duracion,
			$pelicula->director,
			$pelicula->reparto,
			$pelicula->sinopsis,
			

		]);
		echo json_encode(['message'=>'Su petición fue procesada correctamente.']);
	}catch(PDOException $e){
		http_response_code(500);
		echo json_encode(['message'=>'No se pudo completar la peticion','error'=>$e->getMessage()]);
		
	}
}

function handlePut($conn) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id > 0) {
        // Obtener datos del cuerpo de la solicitud (JSON)
        $data = json_decode(file_get_contents('php://input'), true);

        // Validar que se hayan proporcionado los campos requeridos
        $requiredFields = ['titulo', 'genero', 'fecha_lanzamiento', 'duracion', 'director', 'reparto', 'sinopsis'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                http_response_code(400);
                echo json_encode(['message' => "Falta el campo requerido: $field"]);
                return;
            }
        }

        // Crear objeto Pelicula desde los datos recibidos
        $pelicula = Peliculas::fromArray($data);
        $pelicula->id = $id;

        // Construir la consulta SQL para actualizar la película
        $fields = [];
        $params = [];

        if ($pelicula->titulo !== null) {
            $fields[] = 'titulo = ?';
            $params[] = $pelicula->titulo;
        }
        if ($pelicula->genero !== null) {
            $fields[] = 'genero = ?';
            $params[] = $pelicula->genero;
        }
        if ($pelicula->fecha_lanzamiento !== null) {
            $fields[] = 'fecha_lanzamiento = ?';
            $params[] = $pelicula->fecha_lanzamiento;
        }
        if ($pelicula->duracion !== null) {
            $fields[] = 'duracion = ?';
            $params[] = $pelicula->duracion;
        }
        if ($pelicula->director !== null) {
            $fields[] = 'director = ?';
            $params[] = $pelicula->director;
        }
        if ($pelicula->reparto !== null) {
            $fields[] = 'reparto = ?';
            $params[] = $pelicula->reparto;
        }
        if ($pelicula->sinopsis !== null) {
            $fields[] = 'sinopsis = ?';
            $params[] = $pelicula->sinopsis;
        }

        // Ejecutar la consulta de actualización
        if (!empty($fields)) {
            $params[] = $id; // Agregar el ID al final del array de parámetros
            $sql = "UPDATE peliculas SET " . implode(', ', $fields) . " WHERE id = ?";
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                echo json_encode(['message' => 'Pelicula modificada exitosamente.']);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['message' => 'Error al intentar actualizar la película', 'error' => $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['message' => 'No se proporcionaron datos para actualizar']);
        }

    } else {
        http_response_code(400);
        echo json_encode(['message' => 'ID de película no válido']);
    }
}


function handleDelete($conn) {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0) {
        try {
            $stmt = $conn->prepare("DELETE FROM peliculas WHERE id = ?");
            $stmt->execute([$id]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['message' => 'Película eliminada con éxito']);
            } else {
                http_response_code(404);
                echo json_encode(['message' => 'No se encontró la película']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['message' => 'Error al eliminar la película: ' . $e->getMessage()]);
        }
    } else {
        http_response_code(400);
        echo json_encode(['message' => 'ID de película no válido']);
    }
}

?>