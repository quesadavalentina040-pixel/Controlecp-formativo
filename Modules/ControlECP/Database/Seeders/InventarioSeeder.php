<?php

namespace Modules\ControlECP\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\ControlECP\Entities\CategoriaInventario;
use Modules\ControlECP\Entities\Inventario;
use Modules\ControlECP\Entities\MovimientoInventario;
use Modules\ControlECP\Entities\RecursoFoto;
use Modules\ControlECP\Entities\RecursoNovedad;

class InventarioSeeder extends Seeder
{
    /**
     * Datos iniciales de demostración para el inventario de Control ECP.
     */
    public function run(): void
    {
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        $catPapeleria = CategoriaInventario::firstOrCreate(['nombre' => 'Papelería y Útiles Escolares']);
        $catArte      = CategoriaInventario::firstOrCreate(['nombre' => 'Materiales de Arte y Expresión']);
        $catMobiliario = CategoriaInventario::firstOrCreate(['nombre' => 'Mobiliario Pedagógico']);
        $catTecnologia = CategoriaInventario::firstOrCreate(['nombre' => 'Equipos Tecnológicos y Audiovisuales']);
        $catJuegos    = CategoriaInventario::firstOrCreate(['nombre' => 'Juegos y Dinámicas de Paz']);

        $muestras = [
            [
                'cat'       => $catPapeleria->id_categoria_inventario,
                'nombre'    => 'Cartilla Guía "Convivencia y Cultura de Paz"',
                'total'     => 80,
                'disp'      => 75,
                'crit'      => 10,
                'reg'       => 25,
                'estado_f'  => 'Bueno',
                'obs'       => 'Ubicada en Estante 1 - Material pedagógico para talleres presenciales.',
                'img'       => 'general/assets/img/carrusel-1.png',
            ],
            [
                'cat'       => $catPapeleria->id_categoria_inventario,
                'nombre'    => 'Resmas de Papel Bond Carta 75g',
                'total'     => 30,
                'disp'      => 6,
                'crit'      => 5,
                'reg'       => 15,
                'estado_f'  => 'Bueno',
                'obs'       => 'Bodega de apoyo - Próxima a requerir reabastecimiento.',
                'img'       => 'general/assets/img/carrusel-2.png',
            ],
            [
                'cat'       => $catArte->id_categoria_inventario,
                'nombre'    => 'Kits de Pintura Acrílica para Murales Comunitarios',
                'total'     => 20,
                'disp'      => 3,
                'crit'      => 4,
                'reg'       => 8,
                'estado_f'  => 'Regular',
                'obs'       => 'Varios tubos de pintura ya están gastados. Nivel crítico de existencias.',
                'img'       => 'general/assets/img/carrusel-3.png',
            ],
            [
                'cat'       => $catMobiliario->id_categoria_inventario,
                'nombre'    => 'Tablero Acrílico Móvil de Mediación',
                'total'     => 6,
                'disp'      => 6,
                'crit'      => 1,
                'reg'       => 2,
                'estado_f'  => 'Bueno',
                'obs'       => 'Ambiente 204 - Con ruedas y borrador magnético.',
                'img'       => 'general/assets/img/paloma-paz.png',
            ],
            [
                'cat'       => $catTecnologia->id_categoria_inventario,
                'nombre'    => 'Bafle de Sonido Portátil con Micrófono Inalámbrico',
                'total'     => 3,
                'disp'      => 0,
                'crit'      => 1,
                'reg'       => 2,
                'estado_f'  => 'Regular',
                'obs'       => 'Todos los bafles están entregados a talleres en ambientes externos.',
                'img'       => 'general/assets/img/sena-empresa-equipo.jpg',
            ],
            [
                'cat'       => $catJuegos->id_categoria_inventario,
                'nombre'    => 'Juego Didáctico de Cartas "Dilemas Éticos y Paz"',
                'total'     => 25,
                'disp'      => 22,
                'crit'      => 5,
                'reg'       => 10,
                'estado_f'  => 'Bueno',
                'obs'       => 'Armario de juegos lúdicos - Excelente estado.',
                'img'       => 'general/assets/img/logo-ecp-round.png',
            ],
        ];

        foreach ($muestras as $m) {
            $rec = Inventario::firstOrCreate(
                [
                    'id_categoria_inventario' => $m['cat'],
                    'nombre'                  => $m['nombre'],
                ],
                [
                    'id_usuario'          => $adminId,
                    'cantidad_total'      => $m['total'],
                    'cantidad_disponible' => $m['disp'],
                    'umbral_critico'      => $m['crit'],
                    'umbral_regular'      => $m['reg'],
                    'estado_fisico'       => $m['estado_f'],
                    'observaciones'       => $m['obs'],
                    'estado'              => $m['disp'] == 0 ? 'Agotado' : 'Disponible',
                ]
            );

            // Foto inicial
            RecursoFoto::firstOrCreate(
                [
                    'id_inventario' => $rec->id_inventario,
                    'ruta'          => $m['img'],
                ]
            );

            // Movimiento inicial de entrada
            MovimientoInventario::firstOrCreate(
                [
                    'id_inventario' => $rec->id_inventario,
                    'tipo'          => 'Entrada',
                ],
                [
                    'id_usuario'      => $adminId,
                    'cantidad'        => $m['total'],
                    'estado_anterior' => null,
                    'estado_nuevo'    => $rec->estado,
                    'observacion'     => 'Carga inicial del recurso en inventario.',
                    'fecha'           => now()->subDays(5),
                ]
            );

            // Novedad de ejemplo para algunos recursos
            if ($m['disp'] < $m['total']) {
                $entregados = $m['total'] - $m['disp'];
                MovimientoInventario::create([
                    'id_inventario'   => $rec->id_inventario,
                    'id_usuario'      => $adminId,
                    'tipo'            => 'Entrega',
                    'cantidad'        => $entregados,
                    'estado_anterior' => 'Disponible',
                    'estado_nuevo'    => $rec->estado,
                    'observacion'     => 'Destino: Taller de Formación Ficha 2856471.',
                    'fecha'           => now()->subDays(2),
                ]);

                RecursoNovedad::firstOrCreate(
                    [
                        'id_inventario' => $rec->id_inventario,
                        'descripcion'   => 'Verificación periódica: unidades operativas y en buen estado.',
                    ],
                    [
                        'id_usuario'    => $adminId,
                        'fecha'         => now()->subDay(),
                    ]
                );
            }
        }
    }
}
