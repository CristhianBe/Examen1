<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GuardarEquipoRequest;
use App\Http\Resources\EquipoResource;
use App\Models\Equipo;
use App\Services\EquipoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EquipoController extends Controller
{
    private const POR_PAGINA = 10;
    private const TOPE_POR_PAGINA = 50;

    public function __construct(private EquipoService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        // El tope lo impone el servidor: per_page=500 se convierte en 50; 0 o negativo en 1
        $porPagina = min(max((int) $request->query('per_page', self::POR_PAGINA), 1), self::TOPE_POR_PAGINA);

        $equipos = $this->service->listar($request->query('q'), $request->query('estado'), $porPagina);

        return EquipoResource::collection($equipos);
    }

    public function show(Equipo $equipo): EquipoResource
    {
        return new EquipoResource($equipo->load('categoria'));
    }

    public function store(GuardarEquipoRequest $request): JsonResponse
    {
        $equipo = $this->service->crear($request->validated());

        return (new EquipoResource($equipo))
            ->response()
            ->setStatusCode(201)
            ->header('Location', route('equipos.show', $equipo));
    }

    public function update(GuardarEquipoRequest $request, Equipo $equipo): EquipoResource
    {
        return new EquipoResource($this->service->actualizar($equipo, $request->validated()));
    }

    public function destroy(Equipo $equipo): Response
    {
        $this->service->eliminar($equipo);

        return response()->noContent();
    }

    public function prestar(Equipo $equipo): EquipoResource
    {
        return new EquipoResource($this->service->prestar($equipo));
    }
}
