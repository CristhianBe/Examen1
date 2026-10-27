import { request } from "./api.js";

export const listarCategorias = () => request("/categorias");

export const listarEquipos = (pagina = 1, porPagina = 6) => {
  const parametros = new URLSearchParams({ page: pagina, per_page: porPagina });
  return request(`/equipos?${parametros}`);
};

// POST /equipos   -> 201 con el equipo creado | 422 con { message, errors }
export const crearEquipo = (datos) => request("/equipos", { method: "POST", body: datos });

// DELETE /equipos/<id>   -> 204 | 404 | 409 con { mensaje, codigo }
export const eliminarEquipo = (id) => request(`/equipos/${id}`, { method: "DELETE" });
