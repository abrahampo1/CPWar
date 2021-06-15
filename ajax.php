<?php
include_once("database.php");
if (!isset($_SESSION["userid"])) {
    session_start();
}
if (isset($_POST["timer"])) {
}
if (isset($_POST["id"])) {
    $sql = "SELECT * FROM partidas WHERE id = 1";
    $do = mysqli_query($link, $sql);
    $resultado = mysqli_fetch_assoc($do);
    $json = json_decode($resultado["ciudades"], true);
    $logjson = json_decode($resultado["log"], true);
    $resultado = null;
    $resultado["nombre"] = $json["ciudades"][$_POST["id"]]["nombre"];
    $cantidad = 0;
    $sql = "SELECT * FROM gente WHERE id = " . $json["ciudades"][$_POST["id"]]["owner"];
    $do = mysqli_query($link, $sql);
    $owner = mysqli_fetch_assoc($do);
    $resultado["owner"] = $owner["nombre"];
    $resultado["pais"] = $owner["pais"];
    $resultado["tropas"]["tipos"] = [];
    $resultado["ocupada"] = ['estado' => $json["ciudades"][$_POST["id"]]["ocupada"]["estado"], 'nombre' => $json["ciudades"][$_POST["id"]]["ocupada"]["nombre"], 'inicio' => $json["ciudades"][$_POST["id"]]["ocupada"]["inicio"], 'final' => $json["ciudades"][$_POST["id"]]["ocupada"]["final"], 'ahora' => time()];
    for ($i = 0; $i != count($json["ciudades"][$_POST["id"]]["tropas"]); $i++) {
        $cantidad = $cantidad + $json["ciudades"][$_POST["id"]]["tropas"][$i]["cantidad"];
        $resultado["tropas"]["tipos"][] = ['nombre' => $json["ciudades"][$_POST["id"]]["tropas"][$i]["nombre"], 'id' => $json["ciudades"][$_POST["id"]]["tropas"][$i]["id"], 'cantidad' => $json["ciudades"][$_POST["id"]]["tropas"][$i]["cantidad"]];
    }
    $resultado["tropas"]["total"] = $cantidad;

    //botones
    $userid = $_SESSION["userid"];
    if ($json["ciudades"][$_POST["id"]]["owner"] == $userid) {
        $resultado["botones"] = '<button type="button">Mover</button>
        <button type="button">Dividir</button>
        <button type="button" onclick="comprartropa(1,' . $_POST["id"] . ')">Comprar Tropas</button>';
    } else if (count($logjson["gente"][$userid]["propiedades"]) == 0) {
        $resultado["botones"] = '<button type="button" onclick="starthere(' . $_POST["id"] . ')">Empezar aqui</button>';
    } else {
        $resultado["botones"] = '<button type="button">Conquistar</button>';
    }
    echo json_encode($resultado);
}

if (isset($_POST["start"])) {
    $sql = "SELECT * FROM partidas WHERE id = 1";
    $do = mysqli_query($link, $sql);
    $resultado = mysqli_fetch_assoc($do);
    $actividad = $resultado["actividad"];
    $json = json_decode($resultado["ciudades"], true);
    $libre = $json["ciudades"][$_POST["start"]]["libre"];
    if ($libre == true) {

        $json["ciudades"][$_POST["start"]]["libre"] = false;
        $ciudad = $json["ciudades"][$_POST["start"]]["nombre"];
        $json["ciudades"][$_POST["start"]]["owner"] = $_SESSION["userid"];
        $json = json_encode($json);
        $sql = "UPDATE `partidas` SET `ciudades` = '$json' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        $json = json_decode($resultado["log"], true);
        $nombre = $json["gente"][$_SESSION["userid"]]["nombre"];
        $json["gente"][$_SESSION["userid"]]["propiedades"][$_POST["start"]] = $_POST["start"];
        $actividad .= "<p>" . $nombre . " ha empezado en " . $ciudad . "</p>";
        $json = json_encode($json);
        $sql = "UPDATE `partidas` SET `actividad` = '$actividad' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        $sql = "UPDATE `partidas` SET `log` = '$json' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        echo "OK";
    }
}

if (isset($_POST["log"])) {
    $sql = "SELECT * FROM partidas WHERE id = 1";
    $do = mysqli_query($link, $sql);
    $resultado = mysqli_fetch_assoc($do);
    $data = null;
    $json = json_decode($resultado["log"], true);
    $usuario = $json["gente"][$_SESSION["userid"]];
    $data["mano"] = [];
    foreach ($usuario["propiedades"] as $ciudad) {
        $sql = "SELECT * FROM ciudades WHERE id = " . $ciudad;
        $do = mysqli_query($link, $sql);
        $ciudad_sql = mysqli_fetch_assoc($do);
        $data["mano"][] = ['id' => $ciudad, 'nombre' => $ciudad_sql["nombre"]];
    }
    $data["actividad"] = $resultado["actividad"];
    $data["dinero"] = $usuario["dinero"];
    $data["tiempo"] = time();
    $tareasnuevas = [];
    for ($i = 0; $i != count($usuario["tareas"]); $i++) {
        $sql = "SELECT * FROM tareas WHERE id = " . $usuario["tareas"][$i]["id"];
        $do = mysqli_query($link, $sql);
        $tarea = mysqli_fetch_assoc($do);
        if (isset($tarea["end"])) {
            if (time() > $tarea["end"]) {
                $ciudad = json_decode($resultado["log"], true);
            } else {
                $tareasnuevas[] = ["id" => $tarea["id"], 'ciudad' => $usuario["tareas"][$i]["ciudad"]];
            }
        }
    }
    $usuario["tareas"] = $tareasnuevas;
    $json["gente"][$_SESSION["userid"]] = $usuario;
    $json = json_encode($json);
    $sql = "UPDATE `partidas` SET `log` = '$json' WHERE `partidas`.`id` = 1;";
    mysqli_query($link, $sql);
    

    echo json_encode($data);
}
if (isset($_POST["tiempo"])) {
    echo time();
}

if (isset($_POST["comprar"]) && isset($_POST["ciudad"])) {
    $sql = "SELECT * FROM tropas WHERE id =" . $_POST["comprar"];
    $do = mysqli_query($link, $sql);
    $result = mysqli_fetch_assoc($do);
    $tiempo = $result["tiempo"];
    $costo_tropa = $result["costo"];
    $ahora = time();
    $termina = $ahora + $tiempo;
    $tropa = $result["nombre"];
    $sql = "INSERT INTO `tareas` (`id`, `start`, `end`, `nombre`) VALUES (NULL, '$ahora', '$termina', 'Comprado $tropa');";
    $data["ahora"] = time();
    $data["final"] = $termina;
    if (mysqli_query($link, $sql)) {
        $sql = "SELECT * FROM partidas WHERE id = 1";
        $do = mysqli_query($link, $sql);
        $resultado = mysqli_fetch_assoc($do);
        $json = json_decode($resultado["log"], true);
        $json["gente"][$_SESSION["userid"]]["tareas"][] = ['id' => mysqli_insert_id($link), 'ciudad' => $_POST["ciudad"]];
        $json["gente"][$_SESSION["userid"]]["dinero"] = $json["gente"][$_SESSION["userid"]]["dinero"] - $costo_tropa;
        $jsonciudad = json_decode($resultado["ciudades"], true);
        $jsonciudad["ciudades"][$_POST["ciudad"]]["ocupada"] = ['estado' => mysqli_insert_id($link), 'nombre' => $tropa, 'inicio' => $ahora, 'final' => $termina];
        $json = json_encode($json);
        $jsonciudad = json_encode($jsonciudad);
        $sql = "UPDATE `partidas` SET `log` = '$json' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        $sql = "UPDATE `partidas` SET `ciudades` = '$jsonciudad' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        echo json_encode($data);
    }
}
