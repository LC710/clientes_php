<?php

// session_start()

// Tentar fazer a comunicação com o banco de dados;
try {
    // $conn = new PDO('sqlite:bancodedados.sqlite');
    $pdo = new PDO('sqlite:bancodedados.sqlite');

    // Ativar o modo de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  } catch(PDOException $e) {
    // erro na conexão
    $error = $e->getMessage();
    echo "Erro: $error";
  }


  // -- id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
// Vai Criar uma Tablea "site" se ela não existir;
$pdo->exec("CREATE TABLE IF NOT EXISTS clients (
              id INTEGER PRIMARY KEY AUTOINCREMENT,
              name VARCHAR(100),
              email VARCHAR(100),
              phone VARCHAR(20),
              city VARCHAR(50)
          )");

// Se não tiver nenhum registro, vamos criar alguns
if(!$pdo->query("SELECT * FROM clients")->fetch()){
  $pdo->exec("INSERT INTO clients (name, email, phone, city)
              VALUES 
              ('Fulano', 'fulano@gmail.com', '9999-9999', 'Gothan City'),
              ('Ciclano', 'ciclano@gmail.com', '8888-8888', 'Gothan City'),
              ('Beltrano', 'beltrano@gmail.com', '7777-7777', 'New York')");
            
}


// PROCESS

$data = $_POST;

// Verifique o tipo da operação enviada no POST/GET (ou através de um parâmetro no JSON recebido)
$type = $_POST['type'] ?? '';

// MODIFICAÇÕES NO BANCO

if(!empty($data)){

  // Criar Cliente
  if($data["type"] === "create"){ // Esse é o input "hidden" que foi colocado no "form" pra saber se estamos recebendo dados do "form para criar" ou do "form para editar";

    $name = $data["name"];
    $email = $data["email"];
    $phone = $data["phone"];
    $city = $data["city"];

    $query = "INSERT INTO clients (name, email, phone, city) VALUES (:name, :email, :phone, :city)";
    
    $stmt = $pdo->prepare($query);


    $stmt->bindParam(":name", $name);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":phone", $phone);
    $stmt->bindParam(":city", $city);

    try {
      $stmt->execute();
      $_SESSION["msg"] = "Cliente Criado com Sucesso!";

    } catch(PDOException $e){

      // erro na conexão
      $error = $e->getMessage();
      echo "Erro: $error";
    }
    
  }else if($data["type"] === "edit") {

      $id = $data["id"];
      $name = $data["name"];
      $email = $data["email"];
      $phone = $data["phone"];
      $city = $data["city"];

      $query = "UPDATE clients 
                SET name = :name, email = :email, phone = :phone, city = :city 
                WHERE id = :id";

      $stmt = $pdo->prepare($query);

      $stmt->bindParam(":id", $id);
      $stmt->bindParam(":name", $name);
      $stmt->bindParam(":email", $email);
      $stmt->bindParam(":phone", $phone);
      $stmt->bindParam(":city", $city);

      try {

        $stmt->execute();
        $_SESSION["msg"] = "Cliente atualizado com sucesso!";
    
      } catch(PDOException $e) {
        // erro na conexão
        $error = $e->getMessage();
        echo "Erro: $error";
      }

    } else if($data["type"] === "delete") {

      $id = $data["id"];

      $query = "DELETE FROM clients WHERE id = :id";

      $stmt = $pdo->prepare($query);

      $stmt->bindParam(":id", $id);
      
      try {

        $stmt->execute();
        $_SESSION["msg"] = "Cliente removido com sucesso!";
    
      } catch(PDOException $e) {
        // erro na conexão
        $error = $e->getMessage();
        echo "Erro: $error";
      }

    }

    // Redirect HOME
    // header("Location:" . $BASE_URL . "../index.php");

  // SELEÇÃO DE DADOS
} else {
    
    // $id;

    // if(!empty($_GET)) {
    //   $id = $_GET["id"];
    // }

    // Retorna o dado de um contato
    // if(!empty($id)) {

    //   $query = "SELECT * FROM clients WHERE id = :id";

    //   $stmt = $conn->prepare($query);

    //   $stmt->bindParam(":id", $id);

    //   $stmt->execute();

    //   $contact = $stmt->fetch();

    // } else {

      // Retorna todos os contatos
      // $contacts = [];

      // $query = "SELECT * FROM contacts";

      // $stmt = $conn->prepare($query);

      // $stmt->execute();
      
      // $contacts = $stmt->fetchAll();
      
      // }
      
  }
  
$clients = [];

$query = "SELECT * FROM clients";  

$stmt = $pdo->prepare($query);

$stmt->execute();

$clients = $stmt->fetchAll();

// FECHAR CONEXÃO
$pdo = null;

// Responder com Json:

header('Content-Type: application/json'); // Sem ele vamos ter que usar o "JSON.Parse()" lá no "FRONT" Quando recebermos a resoposta; Podemos ver no "DevTools" em indo em "Network" clique no "Name" que os que foram sem (header('Content-Type: application/json');) está com "application/json" e os que estão sem estão com "text/html; charset=UTF-8";
echo json_encode(['success' => true, 'message' => 'Tudo Certo', 'clientes' => $clients]);
exit;