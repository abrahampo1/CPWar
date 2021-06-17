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
    foreach ($json["ciudades"][$_POST["id"]]["tropas"] as $tropa) {
        $cantidad = $cantidad + $json["ciudades"][$_POST["id"]]["tropas"][$tropa["id"]]["cantidad"];
        $resultado["tropas"]["tipos"][] = ['nombre' => $json["ciudades"][$_POST["id"]]["tropas"][$tropa["id"]]["nombre"], 'id' => $json["ciudades"][$_POST["id"]]["tropas"][$tropa["id"]]["id"], 'cantidad' => $json["ciudades"][$_POST["id"]]["tropas"][$tropa["id"]]["cantidad"]];
    }
    $resultado["tropas"]["total"] = $cantidad;

    //botones
    $userid = $_SESSION["userid"];
    if ($json["ciudades"][$_POST["id"]]["owner"] == $userid) {
        $resultado["botones"] = '<button type="button" onclick="movertropas(' . $_POST["id"] . ')">Mover</button>
        <button type="button">Dividir</button>
        <button type="button" onclick="comprartropa(1,' . $_POST["id"] . ')">Comprar Tropas</button>';
    } else if (count($logjson["gente"][$userid]["propiedades"]) == 0) {
        $resultado["botones"] = '<button type="button" onclick="starthere(' . $_POST["id"] . ')">Empezar aqui</button>';
    } else if(isset($json["ciudades"][$_POST["id"]]["tropas"])){
        $resultado["botones"] = '<button type="button" onclick="conquistar('.$_POST['id'].')">Conquistar</button>';
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
        $json["gente"][$_SESSION["userid"]]["propiedades"][$_POST["start"]] = ['id' => $_POST["start"], 'nombre' => $ciudad];
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
    $ciudad = null;
    foreach ($usuario["propiedades"] as $ciudad) {
        $sql = "SELECT * FROM ciudades WHERE id = " . $ciudad["id"];
        $do = mysqli_query($link, $sql);
        $ciudad_sql = mysqli_fetch_assoc($do);
        $data["mano"][] = ['id' => $ciudad["id"], 'nombre' => $ciudad_sql["nombre"]];
    }
    $data["actividad"] = $resultado["actividad"];
    $data["dinero"] = $usuario["dinero"];
    $data["tiempo"] = time();
    $tareasnuevas = [];
    //var_dump($usuario["tareas"]);
    for ($i = 0; $i != count($usuario["tareas"]); $i++) {
        $sql = "SELECT * FROM tareas WHERE id = " . $usuario["tareas"][$i]["id"];
        $do = mysqli_query($link, $sql);
        $tarea = mysqli_fetch_assoc($do);
        if (isset($tarea["end"])) {
            if (time() > $tarea["end"]) {
                $ciudad = json_decode($resultado["ciudades"], true);
                $ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["ocupada"] = ['estado' => null, 'nombre' => null, 'inicio' => null, 'final' => null, 'ahora' => time(), 'tropas' => []];
                if($tarea["conquistar"] != null){
                    $ciudadconquistar = $tarea["conquistar"];
                    if($ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["owner"] != $_SESSION["userid"]){
                        $tropas = false;
                        $tropas_user = 0;
                        $tropas_total = 0;
                        foreach($ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["tropas"] as $tropa){
                            $tropas = true;
                            $tropas_total += $tropa["cantidad"];
                            if($tropa["owner"] == $_SESSION["userid"]){
                                $tropas_user += $tropa["cantidad"];
                            }
                        }
                        if($tropas_total - $tropas_user <= 0){
                            $ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["owner"] = $_SESSION["userid"];
                            $usuario["propiedades"][$usuario["tareas"][$i]["ciudad"]["id"]] = ['id' => $ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["id"], 'nombre' => $ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["nombre"]];
                        }
                    }
                }
                if ($tarea["moveto"] != null) {
                    $moveto = $tarea["moveto"];
                    $movefrom = $tarea["movefrom"];
                    $movetropa = $tarea["moveid"];
                    $movecant = $tarea["movecant"];
                    if (isset($ciudad["ciudades"][$moveto]["tropas"][$movetropa]["cantidad"])) {
                        $ciudad["ciudades"][$moveto]["tropas"][$movetropa]["cantidad"] = $ciudad["ciudades"][$moveto]["tropas"][$movetropa]["cantidad"] + $movecant;
                    } else {
                        $sql = "SELECT * FROM tropas WHERE id = " . $movetropa;
                        $do = mysqli_query($link, $sql);
                        $tropainfo = mysqli_fetch_assoc($do);
                        $ciudad["ciudades"][$moveto]["tropas"][$movetropa] = ['nombre' => $tropainfo["nombre"], 'cantidad' => $movecant, 'id' => $tropainfo["id"], 'owner' => $_SESSION["userid"]];
                    }
                    $ciudad["ciudades"][$movefrom]["tropas"][$movetropa]["cantidad"] = $ciudad["ciudades"][$movefrom]["tropas"][$movetropa]["cantidad"] - $movecant;
                }
                if ($tarea["tropaid"] != null) {
                    $sql = "SELECT * FROM tropas WHERE id = " . $tarea["tropaid"];
                    $do = mysqli_query($link, $sql);
                    $tropainfo = mysqli_fetch_assoc($do);
                    if (isset($ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["tropas"][$tropainfo["id"]])) {
                        $nuevacantidad = $ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["tropas"][$tropainfo["id"]]["cantidad"] + $tropainfo["cantidad"];
                        $ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["tropas"][$tropainfo["id"]] = ['nombre' => $tarea["nombre"], 'cantidad' => $nuevacantidad, 'id' => $tarea["tropaid"], 'owner' => $_SESSION["userid"]];
                    } else {
                        $ciudad["ciudades"][$usuario["tareas"][$i]["ciudad"]]["tropas"][$tropainfo["id"]] = ['nombre' => $tarea["nombre"], 'cantidad' => $tropainfo["cantidad"], 'id' => $tarea["tropaid"], 'owner' => $_SESSION["userid"]];
                    }
                }
                $ciudad = json_encode($ciudad);
                $sql = "UPDATE `partidas` SET `ciudades` = '$ciudad' WHERE `partidas`.`id` = 1;";
                mysqli_query($link, $sql);
                $sql = "DELETE FROM `tareas` WHERE `tareas`.`id` = ".$tarea["id"];
                mysqli_query($link, $sql);
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
    $tropaid = $result["id"];
    $sql = "INSERT INTO `tareas` (`id`, `start`, `end`, `nombre`, `tropaid`) VALUES (NULL, '$ahora', '$termina', '$tropa', '$tropaid');";
    $data["ahora"] = time();

    $data["final"] = $termina;
    if (mysqli_query($link, $sql)) {

        $tareaid = mysqli_insert_id($link);
        $sql = "SELECT * FROM partidas WHERE id = 1";
        $do = mysqli_query($link, $sql);
        $resultado = mysqli_fetch_assoc($do);

        $json = json_decode($resultado["log"], true);
        $json["gente"][$_SESSION["userid"]]["tareas"][] = ['id' => $tareaid, 'ciudad' => $_POST["ciudad"]];
        $json["gente"][$_SESSION["userid"]]["dinero"] = $json["gente"][$_SESSION["userid"]]["dinero"] - $costo_tropa;
        $jsonciudad = json_decode($resultado["ciudades"], true);
        $jsonciudad["ciudades"][$_POST["ciudad"]]["ocupada"] = ['estado' => $tareaid, 'nombre' => $tropa, 'inicio' => $ahora, 'final' => $termina];
        $json = json_encode($json);
        $jsonciudad = json_encode($jsonciudad);
        $sql = "UPDATE `partidas` SET `log` = '$json' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        $sql = "UPDATE `partidas` SET `ciudades` = '$jsonciudad' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        echo json_encode($data);
    }
}

if (isset($_POST["moveto"]) && isset($_POST["movefrom"])) {
    $tiempo = 20;
    $ahora = time();
    $termina = $ahora + $tiempo;
    $movefrom = $_POST["movefrom"];
    $moveto = $_POST["moveto"];
    $sql = "INSERT INTO `tareas` (`id`, `start`, `end`, `nombre`, `tropaid`, `movefrom`, `moveto`, `moveid`, `movecant`) VALUES (NULL, '$ahora', '$termina', 'Moviendose', '', $movefrom, $moveto, 1, 1000);";
    $data["ahora"] = time();
    $data["final"] = $termina;
    if (mysqli_query($link, $sql)) {
        $tareaid = mysqli_insert_id($link);
        $sql = "SELECT * FROM partidas WHERE id = 1";
        $do = mysqli_query($link, $sql);
        $resultado = mysqli_fetch_assoc($do);
        $json = json_decode($resultado["log"], true);
        $json["gente"][$_SESSION["userid"]]["tareas"][] = ['id' => $tareaid, 'ciudad' => $_POST["movefrom"]];
        $jsonciudad = json_decode($resultado["ciudades"], true);
        $jsonciudad["ciudades"][$_POST["movefrom"]]["ocupada"] = ['estado' => $tareaid, 'nombre' => 'Moviendo tropas', 'inicio' => $ahora, 'final' => $termina];
        if (isset($jsonciudad["ciudades"][$movefrom]["tropas"][1]["cantidad"])) {
            if ($jsonciudad["ciudades"][$movefrom]["tropas"][1]["cantidad"] >= 1000) {
                $json = json_encode($json);
                $jsonciudad = json_encode($jsonciudad);
                $sql = "UPDATE `partidas` SET `log` = '$json' WHERE `partidas`.`id` = 1;";
                mysqli_query($link, $sql);
                $sql = "UPDATE `partidas` SET `ciudades` = '$jsonciudad' WHERE `partidas`.`id` = 1;";
                mysqli_query($link, $sql);
                echo json_encode($data);
            }
        } else {
            echo "cant error";
        }
    }
}
if(isset($_POST["conquistar"])){
    $tiempo = 20;
    $ahora = time();
    $termina = $ahora + $tiempo;
    $conquistar = $_POST["conquistar"];
    $sql = "INSERT INTO `tareas` (`id`, `start`, `end`, `nombre`, `conquistar`) VALUES (NULL, '$ahora', '$termina', 'Conquistar', $conquistar);";
    if (mysqli_query($link, $sql)) {
        $tareaid = mysqli_insert_id($link);
        $sql = "SELECT * FROM partidas WHERE id = 1";
        $do = mysqli_query($link, $sql);
        $resultado = mysqli_fetch_assoc($do);
        $json = json_decode($resultado["log"], true);
        $json["gente"][$_SESSION["userid"]]["tareas"][] = ['id' => $tareaid, 'ciudad' => $conquistar];
        $jsonciudad = json_decode($resultado["ciudades"], true);
        $jsonciudad["ciudades"][$conquistar]["ocupada"] = ['estado' => $tareaid, 'nombre' => 'Asediando', 'inicio' => $ahora, 'final' => $termina];
        if (isset($jsonciudad["ciudades"][$conquistar]["tropas"][1]["cantidad"])) {
            if ($jsonciudad["ciudades"][$conquistar]["tropas"][1]["cantidad"] >= 1000) {
                $json = json_encode($json);
                $jsonciudad = json_encode($jsonciudad);
                $sql = "UPDATE `partidas` SET `log` = '$json' WHERE `partidas`.`id` = 1;";
                mysqli_query($link, $sql);
                $sql = "UPDATE `partidas` SET `ciudades` = '$jsonciudad' WHERE `partidas`.`id` = 1;";
                mysqli_query($link, $sql);
            }
        } else {
            echo "cant error";
        }
    }else{
        echo mysqli_error($link);
    }
}