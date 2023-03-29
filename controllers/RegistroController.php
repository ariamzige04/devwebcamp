<?php

namespace Controllers;

use Model\Dia;
use Model\Hora;
use MVC\Router;
use Model\Evento;
use Model\Paquete;
use Model\Ponente;
use Model\Usuario;
use Model\Registro;
use Model\Categoria;
use Model\EventosRegistros;
use Model\Regalo;

class RegistroController {

    public static function crear(Router $router) {

        if(!is_auth()) {
            header('Location: /');
            return;
        }

        // Verificar si el usuario ya esta registrado
        $registro = Registro::where('usuario_id', $_SESSION['id']);

        //si el usuario ya esta registrado y eligio el plan gratis "3" te redirecciona a tu boleto
        //si eligio el plan virtual "2" tambien  (|| esto es un "o", && es "y")
        if(isset($registro) && ($registro->paquete_id === "3" || $registro->paquete_id === "2" )) {
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        }

        //si el usuario ya esta registrado y eligio el paquete presencial "1" te redirecciona a otra pagina para ver las conferencias
        if(isset($registro) && $registro->paquete_id === "1") {
            header('Location: /finalizar-registro/conferencias');
            return;
        }

        $router->render('registro/crear', [
            'titulo' => 'Finalizar Registro'
        ]);
    }

    public static function gratis(Router $router) {

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
                return;
            }

            // 1 es "presencial", 2 "virtual" y 3 "gratis"

            // Verificar si el usuario ya esta registrado
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if(isset($registro) && $registro->paquete_id === "3") {
                header('Location: /boleto?id=' . urlencode($registro->token));
                return;
            }

            $token = substr( md5(uniqid( rand(), true )), 0, 8);//substr corta desde el inicio "0" hasta el "8" caracter (esto para que no salgauna cadena muy larga)
            
            // Crear registro
            $datos = [
                'paquete_id' => 3,
                'pago_id' => '',
                'token' => $token,
                'usuario_id' => $_SESSION['id']
            ];

            $registro = new Registro($datos);//crea una nueva instancia en registro con los datos para guardar
            $resultado = $registro->guardar();

            if($resultado) {
                header('Location: /boleto?id=' . urlencode($registro->token));//url encode evita caracteres especiales (la pagina de boleto es una area PUBLICA, cualquiera lo puede ver y compartir)
                return;
            }

        }
    }

    public static function boleto(Router $router) {

        // Validar la URL
        $id = $_GET['id'];//saca el id de la url

        if(!$id || !strlen($id) === 8 ) {//valida si No hay un id o No tiene 8 caracteres
            header('Location: /');
            return;
        }

        // buscarlo en la BD
        $registro = Registro::where('token', $id);//busca el token o sea el id en la BD para a ver si existe
        if(!$registro) {
            header('Location: /');
            return;
        }
        // Llenar las tablas de referencia
        $registro->usuario = Usuario::find($registro->usuario_id);//aqui se pasa el registro del usuario su id y lo busca en el modelo de usuario o tabla y saca la referencia (en ves de su id, saca el nombre)
        $registro->paquete = Paquete::find($registro->paquete_id);

        $router->render('registro/boleto', [
            'titulo' => 'Asistencia a DevWebCamp',
            'registro' => $registro
        ]);
    }


    public static function pagar(Router $router) {

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            if(!is_auth()) {
                header('Location: /login');
                return;
            }

            // Validar que Post no venga vacio
            if(empty($_POST)) {//si vine vacio el post manda un array vacio y ya no se ejecuta el siguiente codigo
                echo json_encode([]);
                return;
            }

            // Crear el registro
            $datos = $_POST;//se guardan los datos del post
            // debuguear($datos);

            $datos['token'] = substr( md5(uniqid( rand(), true )), 0, 8);//se crea el token
            $datos['usuario_id'] = $_SESSION['id'];//en usuario id se guarda el mismo id de la session del usuario (o sea donde inicio sesion)
            
            try {
                $registro = new Registro($datos);//
          
                $resultado = $registro->guardar();
                echo json_encode($resultado);//se pasa el resultado al sript js de paypal
            } catch (\Throwable $th) {
                echo json_encode([
                    'resultado' => 'error'//hubo un error
                ]);
            }

        }
    }



    public static function conferencias(Router $router) {

        if(!is_auth()) {
            header('Location: /login');
            return;
        }        

        // Validar que el usuario tenga el plan presencial
        $usuario_id = $_SESSION['id'];//id del usuario que inicio sesion y que ya esta autenticado
        $registro = Registro::where('usuario_id', $usuario_id);//se busca en la BD

        //              "1" es presencial, "2" es virtual y "3" es gratis
        if(isset($registro) && $registro->paquete_id === "2") {//si es virtual y quiere volver a pagar otra vez, te redirecciona a su boleto
            header('Location: /boleto?id=' . urlencode($registro->token));
            return;
        }
        
        if($registro->paquete_id !== "1") {//si no eligio el presencial redirecciona
            header('Location: /');
            return;//los que ya pagaron el pase presencial se sigue ejecutando el siguiente codigo
        }

        // Redireccionar a boleto virtual en caso de haber finalizado su registro
        // if(isset($registro->regalo_id) && $registro->paquete_id === "1") {//si el registrado ya tiene un regalo escogido y ya pago el pase presencial
        //     header('Location: /boleto?id=' . urlencode($registro->token));
        //     return;
        // }

        //solamente el usuario que pago el pase presencial, se ejecuta el siguiente codigo
        $eventos = Evento::ordenar('hora_id', 'ASC');//del evento que tomo y dependiedo de la hora que aligio 

        $eventos_formateados = [];
        foreach($eventos as $evento) { 
            $evento->categoria = Categoria::find($evento->categoria_id);
            $evento->dia = Dia::find($evento->dia_id);
            $evento->hora = Hora::find($evento->hora_id);
            $evento->ponente = Ponente::find($evento->ponente_id);
            
            if($evento->dia_id === "1" && $evento->categoria_id === "1") {
                $eventos_formateados['conferencias_v'][] = $evento;
            }

            if($evento->dia_id === "2" && $evento->categoria_id === "1") {
                $eventos_formateados['conferencias_s'][] = $evento;
            }

            if($evento->dia_id === "1" && $evento->categoria_id === "2") {
                $eventos_formateados['workshops_v'][] = $evento;
            }

            if($evento->dia_id === "2" && $evento->categoria_id === "2") {
                $eventos_formateados['workshops_s'][] = $evento;
            }
        }
        
        $regalos = Regalo::all('ASC');

        // Manejando el registro mediante $_POST
        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            // Revisar que el usuario este autenticado
            if(!is_auth()) {
                header('Location: /login');
                return;
            }

            $eventos = explode(',', $_POST['eventos']);//aqu es donde recibes los datos de fetch api de eventos, se separa dependiendo de donde esta la coma ","
            if(empty($eventos)) {
                echo json_encode(['resultado' => false]);//si esta vacio retorna un false
                return;
            }

            // Obtener el registro de usuario
            $registro = Registro::where('usuario_id', $_SESSION['id']);
            if(!isset($registro) || $registro->paquete_id !== "1") {//si fue a la BD y no encontro un registro o un usuario, o si es diferente el paquete 1 (pase presencial) o sea que entro pero No pago el pase presencial
                echo json_encode(['resultado' => false]);
                return;
            }

            $eventos_array = [];//el array de eventos
            // Validar la disponibilidad de los eventos seleccionados
            foreach($eventos as $evento_id) {//COMPRUEBA QUE EXISTE EL EVENTO Y QUE HALLA LUGARES DISPONIBLES
                $evento = Evento::find($evento_id);//se busca los eventos que selecciono el usuario
                // Comprobar que el evento exista
                if(!isset($evento) || $evento->disponibles === "0") {//si no hay nada en eventos o si el evento que eliguio ya se agoto rapidamente por los otros usuarios
                    echo json_encode(['resultado' => false]);
                    return;
                }
                $eventos_array[] = $evento;//aqui es donde se llena el array con cada evento
            }

            //aqui ya tiene el objeto en memoria y ya no es necesario gastar recursos en conectarse a la BD y buscar los eventos
            foreach($eventos_array as $evento) {//SUSTRAE Y LUEGO ALMACENA EL REGISTRO
                $evento->disponibles -= 1;//AQUI RESTA UNO DISPONIBLE
                $evento->guardar();//Y SE GUARDA Y ASI SUCESIVAMENTE CON TODOS LOS EVENTOS QUE SELECCIONO EL USUARIO

                // Almacenar el registro
                $datos = [//array de datos
                    'evento_id' =>  (int) $evento->id,//se pueden castear para guardarlos como enteros
                    'registro_id' => (int)  $registro->id
                ];

                $registro_usuario = new EventosRegistros($datos);
                $registro_usuario->guardar();
            }

            // Almacenar el regalo
            $registro->sincronizar(['regalo_id' => $_POST['regalo_id']]);//se sincroniza el regalo
            $resultado = $registro->guardar();

            if($resultado) {
                echo json_encode([
                    'resultado' => $resultado, //pasa el resultado al fronEnd
                    'token' => $registro->token//aqui ya esta generado el token 
                ]);
            } else {
                echo json_encode(['resultado' => false]);
            }

            return;
        }


        $router->render('registro/conferencias', [
            'titulo' => 'Elige Workshops y Conferencias',
            'eventos' => $eventos_formateados,
            'regalos' => $regalos
        ]);
    }


}