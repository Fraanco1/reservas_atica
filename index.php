<?php
    header ('Content-type: text/html; charset=utf-8');
    $database = new mysqli('localhost', 'root', '', 'baseprueba');

    session_start();
    if(!isset($_SESSION['auth_token'])) {
        header('Location: ../login/index.php');
    } else {
        $auth_token = $_SESSION['auth_token'];
        $hashed_auth_token = hash('sha256', $auth_token);
        $id_profesor = $database -> query("SELECT id_profesor FROM profesores WHERE auth_token='{$hashed_auth_token}'") -> fetch_assoc()["id_profesor"];
    }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="main.js"></script>
    <title>Reservas</title>
</head>
<body>
    <div id="contenido">
        <div class="reservar">
            <h1>Reservar aula.</h1>
            <form name="reservar" method="post" action="">
                <label>Seleccionar sala: </label>
                <select name="salas" required>
                    <option value="" selected disabled>Elegí una sala</option>
                    <?php
                        $query = "SELECT * FROM salas";
                        $query = $database -> query($query);
                        $query = $query -> fetch_all();
                        foreach($query as $r) {
                            print "<option value='{$r[0]}'>".utf8_encode($r[1])."</option>";
                        }
                    ?>
                </select>

                <label>Seleccioná la materia: </label>
                <select name="materias" required>
                    <option value="" selected disabled>Elegí una materia</option>
                    <?php   
                        $materias = $database -> query("SELECT id_materia, materia, curso FROM materias WHERE id_profesor='{$id_profesor}'");
                        while($row_materia = $materias -> fetch_assoc()) {
                            $nombre_materia = utf8_encode($row_materia['materia']).", {$row_materia['curso']} año.";
                            print "<option value='{$row_materia['id_materia']}'>{$nombre_materia}</option>";
                        }
                    ?>
                </select>

                <label>Seleccioná la fecha: </label>
                <input type="date" name="fecha" <?php echo 'min="'.date("Y-m-d").'" max="'.date('Y-m-d', strtotime("+1 months")).'"';?> required>

                <label>Seleccionar hora de inicio: </label>
                <select class="horarios" name="hora_inicio" required>
                    <option value="" selected disabled>Elegí hora de inicio</option>
                    <?php
                        $horarios = Array('8:00', '8:40', '9:20', '10:10', 
                                          '10:50', '11:40', '12:20', '13:05', '14:30');
                        foreach($horarios as $hora) {
                            print "<option value='{$hora}'>{$hora}</option>";
                        }
                    ?>
                </select>

                <label>Seleccioná hora de salida: </label>
                <select class="horarios" name="hora_final" required>
                    <option value="" selected disabled>Elegí hora de salida</option>
                    <?php
                        $horarios = Array('8:00', '8:40', '9:20', '10:10', 
                                          '10:50', '11:40', '12:20', '13:05', '14:30');
                        foreach($horarios as $hora) {
                            print "<option value='{$hora}'>{$hora}</option>";
                        }
                    ?>
                </select>
                
                <br>
                <div id="errorReserva"></div>
                <input type="submit" name="submit" value="Reservar">
            </form>
            <?php
            if(isset($_POST["submit"], $_POST["salas"], $_POST["materias"], $_POST["fecha"], $_POST["hora_inicio"], $_POST["hora_final"])) {
                $id_sala = $_POST["salas"];
                $id_materia = $_POST["materias"];
                $fecha = $_POST["fecha"];
                $hora_inicio = $_POST["hora_inicio"];
                $hora_final = $_POST["hora_final"];

                if(strtotime($hora_final) > strtotime($hora_inicio)) {
                    $check_reserva = $database -> query("SELECT hora_inicio, hora_final FROM reservas WHERE id_sala='{$id_sala}' AND fecha='{$fecha}'");
                    if(($check_reserva -> num_rows) == 0) {
                        $database -> query("INSERT INTO reservas VALUES (0, '{$id_sala}', '{$id_profesor}', '{$id_materia}','{$fecha}', '{$hora_inicio}', '{$hora_final}')");
                    } else {
                        $posibilidad_reservar = false;
                        while($row_check_hours = $check_reserva -> fetch_assoc()) {
                            $hora_i_dbcheck = strtotime($row_check_hours["hora_inicio"]);
                            $hora_f_dbcheck = strtotime($row_check_hours["hora_final"]);

                            if(strtotime($hora_inicio) > $hora_f_dbcheck or strtotime($hora_final) < $hora_i_dbcheck) {
                                $posibilidad_reservar = true;
                            } else if(strtotime($hora_inicio) >= $hora_f_dbcheck and strtotime($hora_final) != $hora_i_dbcheck) {
                                $posibilidad_reservar = true;
                            } else if(strtotime($hora_final) <= $hora_i_dbcheck and strtotime($hora_inicio) != $hora_f_dbcheck) {
                                $posibilidad_reservar = true;
                            }  
                            else {
                                $posibilidad_reservar = false;
                                break;
                            }
                        }
                        if($posibilidad_reservar) {
                            $database -> query("INSERT INTO reservas VALUES (0, '{$id_sala}', '{$id_profesor}', '{$id_materia}','{$fecha}', '{$hora_inicio}', '{$hora_final}')");
                        } else{
                            print "<script>ingresoError('La sala ya está reservada.');</script>";
                        }
                    }
                } else {
                    print "<script>ingresoError('Error al ingresar el horario.');</script>";
                }
            }

            if(isset($_SESSION["auth_token"])) {
                print "
                <div id='logout'>
                    <form name='logout' method='post' action=''>
                        <input type='submit' name='logout_button' value='Salir'>
                    </form>
                </div>";
                if(isset($_POST['logout_button'])) {
                    session_destroy();
                    header('Location: ../login/index.php');
                }
            }
            ?>
        </div>

        <div class="reservas">
            <h1>Reservas</h1>
            <table id="reservas-tabla">
                <tr class="table-header">
                    <th>Sala</th>
                    <th>Profesor</th>
                    <th>Materia</th>
                    <th>Curso</th>
                    <th>Fecha</th>
                    <th>Día</th>
                    <th>Hora inicio</th>
                    <th>Hora final</th>
                </tr>

                <?php
                    $rows_reservas = $database -> query("SELECT * FROM reservas ORDER BY fecha ASC, hora_inicio ASC");
                    $contador = 0;

                    $array_reservas = Array();
                    foreach($rows_reservas as $row) {
                        $nombre_sala = utf8_encode($database -> query("SELECT sala FROM salas WHERE id_sala={$row['id_sala']}") -> fetch_assoc()["sala"]);
                        $nombre_profesor = utf8_encode($database -> query("SELECT nombre_apellido FROM profesores WHERE id_profesor={$row['id_profesor']}") -> fetch_assoc()["nombre_apellido"]);
                        $nombre_materia = utf8_encode($database -> query("SELECT materia FROM materias WHERE id_materia={$row['id_materia']}") -> fetch_assoc()['materia']);
                        $curso = $database -> query("SELECT curso FROM materias WHERE id_materia={$row['id_materia']}") -> fetch_assoc()['curso'];
                        $fecha = date_format(date_create($row['fecha']), 'd/m/Y');
                        $days_dias = array(
                            'Monday'=>'Lunes',
                            'Tuesday'=>'Martes',
                            'Wednesday'=>'Miércoles',
                            'Thursday'=>'Jueves',
                            'Friday'=>'Viernes',
                            'Saturday'=>'Sábado',
                            'Sunday'=>'Domingo'
                            );
                        $dia =  $days_dias[date('l', strtotime($row['fecha']))];
                        $hora_inicio_db = $row['hora_inicio'];
                        $hora_final_db = $row['hora_final'];

                        $datos_array = Array(
                            "sala" => $nombre_sala,
                            "profesor" => $nombre_profesor,
                            "materia"  => $nombre_materia,
                            "curso" => $curso.'° año',
                            "fecha" => $fecha,
                            "dia" => $dia,
                            "hora_inicio" => $hora_inicio_db,
                            "hora_final" => $hora_final_db
                        );
                        array_push($array_reservas, $datos_array);
                    }
                    $array_reservas = json_encode($array_reservas);
                ?>
                <script type="text/javascript">
                    var datosTabla = <?php echo $array_reservas;?>;
                </script>
            </table>
            <div id="button-container"></div>
        </div>
    </div>
    <script>
        if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
        }
        mostrarTabla(datosTabla, 9, currentTablePage);
        mostrarBotones(datosTabla, document.querySelector('#button-container'), 9);
        chequeoBotones();
    </script>
</body>
</html>

<?php
    $database -> close();
?>