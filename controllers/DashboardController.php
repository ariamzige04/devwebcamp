<?php

namespace Controllers;

use Model\Evento;
use Model\Registro;
use Model\Usuario;
use MVC\Router;

class DashboardController {

    public static function index(Router $router) {

        // Obtener ultimos registros
        $registros = Registro::get(5);//obtiene los ultimos 5 registrados 
        foreach($registros as $registro) {
            $registro->usuario = Usuario::find($registro->usuario_id);//se crea la llave usuario y busca el registro por su usuario id
        }

        // Calcular los ingresos
        $virtuales = Registro::total('paquete_id', 2);//pase virtual (toma el total de registros)
        $presenciales = Registro::total('paquete_id', 1);//pase presencial

        $ingresos = ($virtuales * 46.41) + ($presenciales * 189.54);//el numero de registros lo multiplica y se suman entre los 2 pases

        // Obtener eventos con más y menos lugares disponibles
        $menos_disponibles = Evento::ordenarLimite('disponibles', 'ASC', 5);//toma una columna, el orden y el limite
        $mas_disponibles = Evento::ordenarLimite('disponibles', 'DESC', 5);


        $router->render('admin/dashboard/index', [
            'titulo' => 'Panel de Administración',
            'registros' => $registros,
            'ingresos' => $ingresos,
            'menos_disponibles' => $menos_disponibles,
            'mas_disponibles' => $mas_disponibles
        ]);
    }
}