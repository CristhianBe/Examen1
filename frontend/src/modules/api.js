import { BASE_URL, TIEMPO_MAXIMO_MS } from "./config.js";
import { ErrorApi } from "./errores.js";
import { obtenerToken, olvidarSesion } from "./auth.js";


export const request = async (ruta, opciones = {}) => {
  const { body, headers = {}, ...resto } = opciones;

  const encabezados = { Accept: "application/json", ...headers };


  const token = obtenerToken();
  if (token) encabezados.Authorization = `Bearer ${token}`;

  let cuerpo = body;
  if (body !== undefined && body !== null && typeof body === "object") {
    encabezados["Content-Type"] = "application/json";
    cuerpo = JSON.stringify(body);
  }


  const controlador = new AbortController();
  const temporizador = setTimeout(() => controlador.abort(), TIEMPO_MAXIMO_MS);

  try {
    const response = await fetch(`${BASE_URL}${ruta}`, {
      ...resto,
      headers: encabezados,
      body: cuerpo,
      signal: controlador.signal,
    });

    if (response.status === 204) return null;

    const datos = await response.json().catch(() => ({}));

    if (!response.ok) {
      if (response.status === 401) {
        olvidarSesion();
        window.location.reload();
      }
      throw new ErrorApi(response.status, datos);
    }

    return datos;
  } finally {
    clearTimeout(temporizador);
  }
};
