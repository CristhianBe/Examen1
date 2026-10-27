import { crearEquipo, listarCategorias } from "../modules/equipos.js";
import { mostrarMensaje } from "./mensajes.js";

const $form = document.querySelector("#form-equipo");
const $selectCategoria = document.querySelector("#categoria_id");
const $btnGuardar = document.querySelector("#btn-guardar");

const cargarCategorias = async () => {
  try {
    const { data } = await listarCategorias();
    for (const categoria of data) {
      const $opcion = document.createElement("option");
      $opcion.value = categoria.id;
      $opcion.textContent = categoria.nombre;
      $selectCategoria.append($opcion);
    }
  } catch {
    mostrarMensaje("error", "No fue posible cargar las categorías.");
  }
};

const limpiarErrores = () => {
  $form.querySelectorAll("[data-error]").forEach(($p) => ($p.textContent = ""));
  $form.querySelectorAll("[aria-invalid]").forEach(($campo) => $campo.removeAttribute("aria-invalid"));
};

const mostrarErrores = (errores) => {
  for (const [campo, [mensaje]] of Object.entries(errores)) {
    const $error = $form.querySelector(`[data-error="${campo}"]`);
    if ($error) $error.textContent = mensaje;
    $form.elements[campo]?.setAttribute("aria-invalid", "true");
  }
};

const leerDatos = () => {
  const datos = Object.fromEntries(new FormData($form));
  datos.categoria_id = Number(datos.categoria_id);
  datos.costo_interno = Number(datos.costo_interno);
  return datos;
};

export const iniciarFormulario = ({ alGuardar }) => {
  cargarCategorias();

  $form.addEventListener("submit", async (evento) => {
    evento.preventDefault();
    limpiarErrores();

    const textoOriginal = $btnGuardar.textContent;
    $btnGuardar.disabled = true;
    $btnGuardar.textContent = "Guardando…";

    try {
      const { data } = await crearEquipo(leerDatos());
      $form.reset();
      mostrarMensaje("exito", `Equipo ${data.codigo} registrado.`);
      alGuardar();
    } catch (error) {
      if (error.status === 422) {
        mostrarErrores(error.datos.errors ?? {});
      } else {
        mostrarMensaje("error", error.message || "No fue posible registrar el equipo.");
      }
    } finally {
      $btnGuardar.disabled = false;
      $btnGuardar.textContent = textoOriginal;
    }
  });
};
