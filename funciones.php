<?php

function empezarpartida()
{
    include("config.php");
    include("database.php");
    $sql = "SELECT * FROM ciudades";
    $do = mysqli_query($link, $sql);
    $json["ciudades"][] = null; //El primer json es null porque esta sync con las ID de cada ciudad
    while ($ciudad = mysqli_fetch_assoc($do)) {
        $json["ciudades"][$ciudad["id"]] = ['nombre' => $ciudad["nombre"], 'owner' => 0,'libre' => true,'ocupada'=>['estado' => null, 'nombre' => null, 'inicio' => null, 'final' => null, 'ahora' => time()], 'tropas' => []];
    }
    $json = json_encode($json);
    $sql = "UPDATE `partidas` SET `ciudades` = '$json' WHERE `partidas`.`id` = 1;";
    if (mysqli_query($link, $sql)) {
    }
    $json = null;
    $sql = "SELECT * FROM gente";
    $do = mysqli_query($link, $sql); //El primer json es null porque esta sync con las ID de cada ciudad
    while ($gente = mysqli_fetch_assoc($do)) {
        $json["gente"][$gente["id"]] = ['nombre' => $gente["nombre"], 'pais' => $gente["pais"], 'dinero' => 1000, 'propiedades' => [], 'tareas' => []];
    }
    $json = json_encode($json);
    $sql = "UPDATE `partidas` SET `log` = '$json' WHERE `partidas`.`id` = 1;";
    if (mysqli_query($link, $sql)) {
        $sql = "UPDATE `partidas` SET `actividad` = '' WHERE `partidas`.`id` = 1;";
        mysqli_query($link, $sql);
        header("location: wargame.php");
    }
}
