# CONTEXTO DEL PROYECTO — SISTEMA ADMINISTRATIVO IBC
# Instituto Boliviano de la Ceguera

---

## VISIÓN GENERAL

Sistema web administrativo para el **Instituto Boliviano de la Ceguera (IBC)** que gestiona personas con discapacidad visual, sus afiliaciones, beneficios sociales, trámites, capacitaciones y rutas seguras accesibles.

**Stack técnico:**
- **Backend:** Laravel 11 (PHP)
- **Frontend:** Blade + Tailwind CSS (CDN) + SweetAlert2
- **Base de datos:** MySQL — `ibc_admin`
- **Autenticación:** Laravel Breeze (Blade)
- **Servidor local:** Laragon (`http://localhost/SISTEMA-IBC-OFICIAL`)

---

## ESTRUCTURA DE CARPETAS RELEVANTE

```
app/
  Http/
    Controllers/        — Un controller por módulo (Resource Controllers)
    Requests/           — Form Requests separados: Store y Update por módulo
  Models/               — Modelos Eloquent con relaciones, scopes y métodos

resources/views/
  layouts/              — Layout principal (app.blade.php) y guest
  [modulo]/
    index.blade.php     — Listado paginado
    create.blade.php    — Formulario crear (incluye partial form)
    edit.blade.php      — Formulario editar (incluye partial form)
    show.blade.php      — Vista detalle (cuando aplica)
    partials/
      form.blade.php    — Formulario reutilizable entre create y edit
      tabla.blade.php   — Tabla del listado con acciones
      filtros.blade.php — Barra de filtros del listado

routes/web.php          — Todas las rutas del sistema (no hay api.php separado)
database/migrations/    — Una migración por tabla, prefijadas con fecha
```

---

## CONVENCIONES CRÍTICAS PARA NUEVOS DESARROLLADORES

### 1. Nomenclatura de campos en BD
Los campos NO siguen camelCase ni snake_case convencional. Usan una nomenclatura abreviada propia:
- `nom_pnv` = nombre de PersonaNoVidente
- `est_afi` = estado de Afiliacion
- `act_iex` = activo de InstitucionExterna
- Formato: `[abrev_campo]_[abrev_tabla]`

**Nunca renombrar campos existentes.** Respetar la nomenclatura tal como está.

### 2. Eliminación lógica (NO física)
Todos los módulos usan eliminación lógica. Hay dos implementaciones:
- **Con `SoftDeletes` de Laravel:** Solo el modelo `Ruta` usa el trait oficial. El campo se llama `deleted_at`.
- **Con campo manual `fbj_*`:** El resto de modelos usa un campo `fbj_[tabla]` (fecha de baja) que se llena con `now()`. Ejemplo: `fbj_pnv`, `fbj_afi`, `fbj_tin`.

**Nunca usar `forceDelete()` ni DELETE físico.**

### 3. Form Requests — obligatorio usar siempre
Toda operación de escritura (store/update) usa un Form Request. Ejemplo:
```
StorePersonaNoVidenteRequest   → se usa en PersonaNoVidenteController@store
UpdatePersonaNoVidenteRequest  → se usa en PersonaNoVidenteController@update
```
Al agregar un nuevo módulo, siempre crear los dos requests correspondientes con `php artisan make:request`. Los mensajes de validación van en MAYÚSCULAS.

### 4. Registro automático del creador
En todo `store()` se debe agregar `registrado_por` o el campo equivalente con `Auth::id()`:
```php
$data = $request->validated();
$data['cod_usuarios_xxx'] = Auth::id();
Modelo::create($data);
```

### 5. IDs nunca visibles en pantalla
En vistas Blade, para obtener la clave primaria de un modelo sin exponer el nombre del campo:
```blade
{{ $modelo->getKey() }}
```

### 6. Rutas en `bootstrap/app.php`, no en `Kernel.php`
Laravel 11 no usa `Kernel.php`. Los middlewares globales y grupos se configuran en `bootstrap/app.php`.

---

## MÓDULOS Y SUS COMPONENTES

---

### MÓDULO 1 — PERSONAS NO VIDENTES + TUTORES

#### Controller: `PersonaNoVidenteController`
Ruta base: `/personas-no-videntes`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/personas-no-videntes` | `index` — listado con filtros (búsqueda, estado, grado, ciudad), paginado 10 |
| GET | `/personas-no-videntes/create` | `create` — formulario nueva persona |
| POST | `/personas-no-videntes` | `store` — crea persona, genera código `ide_pnv`, maneja foto |
| GET | `/personas-no-videntes/{pnv}` | `show` — detalle con tutores, afiliaciones, usuario que registró |
| GET | `/personas-no-videntes/{pnv}/edit` | `edit` — formulario edición |
| PUT | `/personas-no-videntes/{pnv}` | `update` — actualiza, maneja cambio de foto y estado |
| DELETE | `/personas-no-videntes/{pnv}` | `destroy` — soft delete (bloquea si tiene afiliación activa) |

**Vistas:**
- `personas_no_videntes/index.blade.php` → usa `partials/tabla` y `partials/filtros`
- `personas_no_videntes/create.blade.php` → incluye `partials/form`
- `personas_no_videntes/edit.blade.php` → incluye `partials/form`
- `personas_no_videntes/show.blade.php`

**Requests:** `StorePersonaNoVidenteRequest`, `UpdatePersonaNoVidenteRequest`

**Modelo:** `PersonaNoVidente` — tabla `personas_no_videntes`, PK `cod_personas_no_videntes`
- Scopes: `activas()`, `buscar($texto)`
- Métodos: `getNombreCompletoAttribute()`, `tieneAfiliacionActiva()`, `puedeSerAfiliada()`

---

#### Controller: `TutorContactoController`
Ruta base (nested): `/personas-no-videntes/{persona}/tutores`

Este controller es **nested** bajo PersonaNoVidente. Siempre recibe `$persona` como primer parámetro además del `$tutor`.

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/{persona}/tutores` | `index` — lista tutores de esa persona |
| GET | `/{persona}/tutores/create` | `create` — formulario nuevo tutor |
| POST | `/{persona}/tutores` | `store` — crea tutor, maneja tutor legal (`etl_tco`) |
| GET | `/{persona}/tutores/{tutor}/edit` | `edit` — formulario edición |
| PUT | `/{persona}/tutores/{tutor}` | `update` — actualiza tutor |
| DELETE | `/{persona}/tutores/{tutor}` | `destroy` — soft delete vía `fbj_tco` |

**Vistas:** `tutores_contactos/index`, `create`, `edit`, `_form.blade.php`

**Requests:** `StoreTutorContactoRequest`, `UpdateTutorContactoRequest`

**Modelo:** `TutorContacto` — tabla `tutores_contactos`

---

### MÓDULO 2 — AFILIACIONES

#### Controller: `AfiliacionController`
Ruta base: `/afiliaciones`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/afiliaciones` | `index` — listado con filtros, actualiza estados vencidos automáticamente |
| GET | `/afiliaciones/create` | `create` — formulario (requiere permiso) |
| POST | `/afiliaciones` | `store` — crea con código `ide_afi` generado |
| GET | `/afiliaciones/{afi}` | `show` — detalle |
| GET | `/afiliaciones/{afi}/edit` | `edit` — formulario edición |
| PUT | `/afiliaciones/{afi}` | `update` — actualiza, recalcula fecha vencimiento |
| DELETE | `/afiliaciones/{afi}` | `destroy` — soft delete vía `fbj_afi` |

**Lógica especial:** En `index()`, se llama `actualizarEstadoPorVencimiento()` en cada afiliación para marcar como VENCIDA si `fve_afi` ya pasó.

**Vistas:** `afiliaciones/index`, `create`, `edit`, `show` + partials `form`, `tabla`, `filtros`

**Requests:** `StoreAfiliacionRequest`, `UpdateAfiliacionRequest`

**Modelo:** `Afiliacion` — tabla `afiliaciones`, PK `cod_afiliaciones`
- Scopes: `activas()`, `noEliminadas()`, `buscar($texto)`
- Estados posibles (ENUM): `ACTIVA`, `INACTIVA`, `SUSPENDIDA`, `VENCIDA`

---

### MÓDULO 3 — BENEFICIOS SOCIALES + ASIGNACIÓN

#### Controller: `BeneficioController`
Ruta base: `/beneficios`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/beneficios` | `index` — catálogo con filtros, paginado 15 |
| GET | `/beneficios/create` | `create` — solo Admin |
| POST | `/beneficios` | `store` — crea beneficio |
| GET | `/beneficios/{ben}/edit` | `edit` |
| PUT | `/beneficios/{ben}` | `update` |
| DELETE | `/beneficios/{ben}` | `destroy` — soft delete |
| PATCH | `/beneficios/{ben}/toggle` | `toggle` — activa/desactiva el beneficio |

**Vistas:** `beneficios/index`, `create`, `edit` + partials `tabla`, `filtros`

**Requests:** `StoreBeneficioRequest`, `UpdateBeneficioRequest`

**Modelo:** `BeneficioSocial` — tabla `beneficios_sociales`
- Tipos constante: `ECONÓMICO`, `MATERIAL`, `SERVICIO`, `EDUCATIVO`, `OTRO`
- Métodos: `activar()`, `desactivar()`, `darDeBaja()`

---

#### Controller: `AsignacionBeneficioController`
Ruta base: `/asignacion-beneficios`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/asignacion-beneficios` | `index` — asignaciones activas/vigentes, paginado 15 |
| GET | `/asignacion-beneficios/create` | `create` — solo Admin |
| POST | `/asignacion-beneficios` | `store` — valida que no exista asignación activa duplicada |
| GET | `/asignacion-beneficios/{abe}/edit` | `edit` |
| PUT | `/asignacion-beneficios/{abe}` | `update` |
| DELETE | `/asignacion-beneficios/{abe}` | `destroy` |
| GET | `/api/afiliados-buscar` | `buscarAfiliados` — API AJAX para búsqueda en tiempo real |

**Ruta API interna:** `/api/afiliados-buscar?q=texto` devuelve JSON con afiliados activos. Se usa en el formulario create/edit para el campo de búsqueda de afiliado.

**Vistas:** `asignacion_beneficios/index`, `create`, `edit` + partials `tabla`, `filtros`

**Requests:** `StoreAsignacionBeneficioRequest`, `UpdateAsignacionBeneficioRequest`

**Modelo:** `AsignacionBeneficio` — tabla `asignacion_beneficios`
- Estados: `ACTIVO`, `SUSPENDIDO`, `FINALIZADO`
- Método estático: `tieneBeneficioActivo($afiliacion_id, $beneficio_id)`

---

### MÓDULO 4 — TRÁMITES E INSTITUCIONES + ACCESO SALUD

#### Controller: `InstitucionExternaController`
Ruta base: `/instituciones-externas`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/instituciones-externas` | `index` — listado con filtros, paginado 15 |
| GET | `/instituciones-externas/create` | `create` — solo Admin |
| POST | `/instituciones-externas` | `store` — se activa por defecto (`act_iex = true`) |
| GET | `/instituciones-externas/{iex}` | `show` |
| GET | `/instituciones-externas/{iex}/edit` | `edit` |
| PUT | `/instituciones-externas/{iex}` | `update` |
| DELETE | `/instituciones-externas/{iex}` | `destroy` — llama `darDeBaja()` |
| PATCH | `/instituciones-externas/{iex}/toggle` | `toggle` — activa/desactiva |

**Modelo:** `InstitucionExterna` — tabla `instituciones_externas`

---

#### Controller: `TramiteInstitucionController`
Ruta base: `/tramites-instituciones`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/tramites-instituciones` | `index` — filtros por tipo y estado, paginado 15 |
| GET | `/tramites-instituciones/create` | `create` |
| POST | `/tramites-instituciones` | `store` — estado inicial: `PENDIENTE` |
| GET | `/tramites-instituciones/{tin}` | `show` |
| GET | `/tramites-instituciones/{tin}/edit` | `edit` |
| PUT | `/tramites-instituciones/{tin}` | `update` — auto-fecha resolución si pasa a RESUELTO |
| DELETE | `/tramites-instituciones/{tin}` | `destroy` |
| GET | `/api/afiliados-tramites` | `buscarAfiliados` — API AJAX |
| POST | `/tramites-instituciones/{tin}/cambiar-estado` | `cambiarEstado` — transiciones con observaciones |

**Regla de negocio importante:** Un trámite RESUELTO no puede volver a estado PENDIENTE (RN-07).

**Vistas:** `tramites_instituciones/index`, `create`, `edit`, `show` + partials `tabla`, `filtros`

**Requests:** `StoreTramiteInstitucionRequest`, `UpdateTramiteInstitucionRequest`

**Modelo:** `TramiteInstitucion` — tabla `tramites_instituciones`
- Tipos: `SALUD`, `BENEFICIO SOCIAL`, `CAPACITACIÓN`, `LEGAL`, `ADMINISTRATIVO`, `OTRO`
- Estados: `PENDIENTE`, `EN PROCESO`, `RESUELTO`, `RECHAZADO`, `ABANDONADO`

---

#### Controller: `AccesoSaludController`
Ruta base: `/accesos-salud`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/accesos-salud` | `index` — filtro por estado, paginado 15 |
| GET | `/accesos-salud/create` | `create` |
| POST | `/accesos-salud` | `store` |
| GET | `/accesos-salud/{asa}` | `show` |
| GET | `/accesos-salud/{asa}/edit` | `edit` |
| PUT | `/accesos-salud/{asa}` | `update` |
| DELETE | `/accesos-salud/{asa}` | `destroy` |
| GET | `/api/afiliados-accesos` | `buscarAfiliados` — API AJAX |

**Vistas:** `accesos_salud/index`, `create`, `edit`, `show` + partials `tabla`, `filtros`

**Requests:** `StoreAccesoSaludRequest`, `UpdateAccesoSaludRequest`

**Modelo:** `AccesoSalud` — tabla `accesos_salud`
- Estados: `ACTIVO`, `FINALIZADO`, `SUSPENDIDO`

---

### MÓDULO 5 — CAPACITACIONES

#### Controller: `TipoCapacitacionController`
Ruta base: `/tipos-capacitacion`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/tipos-capacitacion` | `index` — búsqueda, paginado 10 |
| GET | `/tipos-capacitacion/create` | `create` — solo Admin |
| POST | `/tipos-capacitacion` | `store` — valida nombre único |
| GET | `/tipos-capacitacion/{tc}/edit` | `edit` |
| PUT | `/tipos-capacitacion/{tc}` | `update` |
| DELETE | `/tipos-capacitacion/{tc}` | `destroy` — bloquea si tiene capacitaciones |
| PATCH | `/tipos-capacitacion/{tc}/toggle` | `toggle` — activa/desactiva |

**Nota:** No usa Form Requests dedicados, usa validación inline con `$request->validate()`.

**Vistas:** `tipos_capacitacion/index`, `create`, `edit`

---

#### Controller: `CapacitacionController`
Ruta base: `/capacitaciones`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/capacitaciones` | `index` — filtros por tipo y estado, paginado 10 |
| GET | `/capacitaciones/create` | `create` — solo Admin |
| POST | `/capacitaciones` | `store` — genera código `ide_cap`, valida tipo activo |
| GET | `/capacitaciones/{cap}` | `show` — detalle con participaciones |
| GET | `/capacitaciones/{cap}/edit` | `edit` |
| PUT | `/capacitaciones/{cap}` | `update` |
| DELETE | `/capacitaciones/{cap}` | `destroy` — bloquea si tiene participaciones activas |
| POST | `/capacitaciones/{cap}/cambiar-estado` | `cambiarEstado` — transiciones de estado |

**Flujo de estados:** `PLANIFICADA` → `EN CURSO` → `FINALIZADA` o `CANCELADA`

**Vistas:** `capacitaciones/index`, `create`, `edit`, `show` + partials `form`, `tabla`, `filtros`, `participantes`

**Requests:** `StoreCapacitacionRequest`, `UpdateCapacitacionRequest`

**Modelo:** `Capacitacion` — tabla `capacitaciones`
- Estados: `PLANIFICADA`, `EN CURSO`, `FINALIZADA`, `CANCELADA`
- Mutators: `nom_cap` y `lug_cap` se convierten automáticamente a mayúsculas

---

#### Controller: `ParticipacionCapacitacionController`
Rutas nested bajo `/capacitaciones/{cap}`:

| Método | Ruta | Acción |
|--------|------|--------|
| POST | `/capacitaciones/{cap}/participaciones` | `store` — inscribe persona, estado inicial: `INSCRITO` |
| PUT | `/capacitaciones/{cap}/participaciones/{part}` | `update` — cambia estado de participación |
| DELETE | `/capacitaciones/{cap}/participaciones/{part}` | `destroy` — elimina participación |
| GET | `/api/personas-buscar` | `buscarPersonas` — API JSON para búsqueda de personas ACTIVAS |

**Flujo de estados participación:** `INSCRITO` → `EN CURSO` → `APROBADO`, `REPROBADO` o `ABANDONÓ`

**Request:** `StoreParticipacionRequest`

---

### MÓDULO 6 — RUTAS SEGURAS

Este es el módulo más complejo. Tiene 7 controllers y una arquitectura jerárquica:
`Ruta` → `CalleTramo` → `CondicionEntorno` / `EvaluacionRiesgo` / `ZonaRiesgo` / `Obstaculo`

#### Controller: `RutaController`
Ruta base: `/rutas`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/rutas` | `index` — filtros, paginado 10 |
| GET | `/rutas/create` | `create` — formulario con mapa |
| POST | `/rutas` | `store` — crea con código `ide_rut`, estado `BORRADOR`, normaliza geo, estima distancia |
| GET | `/rutas/{rut}` | `show` — detalle con tramos, condiciones, evaluaciones, obstáculos, zonas de riesgo |
| GET | `/rutas/{rut}/edit` | `edit` — solo si está en `BORRADOR` o `DESACTUALIZADA` |
| PUT | `/rutas/{rut}` | `update` |
| DELETE | `/rutas/{rut}` | `destroy` — bloquea si tiene tramos (usa SoftDeletes de Laravel) |
| POST | `/rutas/{rut}/cambiar-estado` | `cambiarEstado` — flujo de aprobación |
| POST | `/api/rutas/estimar-trayecto` | `estimarTrayecto` — API JSON: estima distancia/tiempo |
| GET | `/api/rutas/puntos-interes` | `buscarPuntosInteres` — API JSON: puntos de interés |

**Flujo de estados:** `BORRADOR` → `EN REVISIÓN` → `APROBADA` → `PUBLICADA`, también: `DESACTUALIZADA`, `ARCHIVADA`

**Vistas:** `rutas/index`, `create`, `edit`, `show` + partials `form`, `tabla`, `filtros`

**Requests:** `StoreRutaRequest`, `UpdateRutaRequest`

**Modelo:** `Ruta` — tabla `rutas`, usa `SoftDeletes` oficial de Laravel (`deleted_at`)
- Relaciones: `creador()`, `aprobador()`, `tramos()`
- Métodos: `puedePublicarse()`, `nivelSeguridadGeneral()`, `colorEstado()`, `transicionesPosibles()`

---

#### Controller: `TramoController`
Rutas nested bajo `/rutas/{rut}`:

| Método | Ruta | Acción |
|--------|------|--------|
| POST | `/rutas/{rut}/tramos` | `store` — crea tramo en ruta |
| PUT | `/rutas/{rut}/tramos/{tramo}` | `update` |
| DELETE | `/rutas/{rut}/tramos/{tramo}` | `destroy` — bloquea si tiene condiciones/evaluaciones |
| PATCH | `/rutas/{rut}/tramos/reordenar` | `reordenar` — API JSON: reordena tramos por drag & drop |

**Modelo:** `CalleTramo` — tabla `calles_tramos`

---

#### Controller: `CondicionEntornoController`
Rutas nested bajo `/tramos/{tramo}`:

| Método | Ruta | Acción |
|--------|------|--------|
| POST | `/tramos/{tramo}/condiciones` | `store` — registra condición con tipo y nivel |
| DELETE | `/tramos/{tramo}/condiciones/{condicion}` | `destroy` |

**Modelo:** `CondicionEntorno` — tabla `condiciones_entorno`

---

#### Controller: `EvaluacionRiesgoController`
Rutas nested bajo `/tramos/{tramo}`:

| Método | Ruta | Acción |
|--------|------|--------|
| POST | `/tramos/{tramo}/evaluaciones` | `store` — registra evaluación con nivel seguridad y fecha |
| DELETE | `/tramos/{tramo}/evaluaciones/{evaluacion}` | `destroy` |

**Modelo:** `EvaluacionRiesgo` — tabla `evaluaciones_riesgo`

---

#### Controller: `ZonaRiesgoController`
Rutas nested bajo `/tramos/{tramo}`:

| Método | Ruta | Acción |
|--------|------|--------|
| POST | `/tramos/{tramo}/zonas` | `store` — registra zona de riesgo con nivel |
| DELETE | `/tramos/{tramo}/zonas/{zona}` | `destroy` |

**Modelo:** `ZonaRiesgo` — tabla `zonas_riesgo`

---

#### Controller: `ObstaculoController`
Rutas mixtas:

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/obstaculos` | `index` — obstáculos reportados/confirmados/resueltos, paginado 10 |
| POST | `/tramos/{tramo}/obstaculos` | `store` — reporta obstáculo, guarda foto, detecta origen (TECNICO/VOLUNTARIO) |
| PATCH | `/obstaculos/{obs}/confirmar` | `confirmar` — `REPORTADO` → `ACTIVO` |
| PATCH | `/obstaculos/{obs}/resolver` | `resolver` — marca como `RESUELTO` |
| DELETE | `/obstaculos/{obs}` | `rechazar` — rechaza reporte, elimina foto |

**Vista:** `obstaculos/index.blade.php`

**Modelo:** `Obstaculo` — tabla `obstaculos`

---

#### Controller: `NivelSeguridadController`
Ruta base: `/niveles-seguridad`

| Método | Ruta | Acción |
|--------|------|--------|
| GET | `/niveles-seguridad` | `index` — catálogo ordenado |
| GET | `/niveles-seguridad/{ns}/edit` | `edit` — solo Admin |
| PUT | `/niveles-seguridad/{ns}` | `update` — actualiza descripción y color hexadecimal |

**Vista:** `niveles_seguridad/index.blade.php`, `edit.blade.php`

**Modelo:** `NivelSeguridad` — tabla `niveles_seguridad`

---

## SISTEMA DE PERMISOS

Los permisos se verifican usando métodos del modelo `User`. El sistema usa roles:
- `ADMINISTRADOR` — acceso total
- `TECNICO` — gestión operativa
- `TUTOR` — solo visualización y algunas acciones
- `VOLUNTARIO` — acceso limitado

Ejemplo de uso en controller:
```php
if (!auth()->user()->puedeCrearPersonaNoVidente()) {
    abort(403);
}
```

Ejemplo en vista Blade:
```blade
@if(auth()->user()->esAdministrador())
    <a href="{{ route('personas-no-videntes.create') }}">NUEVA PERSONA</a>
@endif
```

Los métodos de permiso están definidos en `app/Models/User.php` y cubren todos los módulos.

---

## PATRÓN DE APIS INTERNAS (AJAX)

Varios módulos tienen endpoints internos para búsqueda en tiempo real (usados en formularios con autocompletado):

| Ruta | Controller | Para qué se usa |
|------|-----------|-----------------|
| GET `/api/afiliados-buscar` | `AsignacionBeneficioController@buscarAfiliados` | Buscar afiliados en asignación de beneficios |
| GET `/api/afiliados-tramites` | `TramiteInstitucionController@buscarAfiliados` | Buscar afiliados en trámites |
| GET `/api/afiliados-accesos` | `AccesoSaludController@buscarAfiliados` | Buscar afiliados en acceso salud |
| GET `/api/personas-buscar` | `ParticipacionCapacitacionController@buscarPersonas` | Buscar personas ACTIVAS para inscribir |
| POST `/api/rutas/estimar-trayecto` | `RutaController@estimarTrayecto` | Estimar distancia/tiempo entre puntos |
| GET `/api/rutas/puntos-interes` | `RutaController@buscarPuntosInteres` | Buscar puntos de interés en mapa |

Todos devuelven JSON. El patrón en las vistas es jQuery AJAX que llama al endpoint y popula un `<select>` o lista dinámica.

---

## PATRÓN DE VISTAS

### Layout
Todas las vistas del sistema extienden `layouts.app`:
```blade
@extends('layouts.app')
@section('content')
    ...
@endsection
```

### Formulario reutilizable
Los formularios de create y edit incluyen el mismo partial:
```blade
{{-- create.blade.php --}}
<form method="POST" action="{{ route('modulo.store') }}">
    @csrf
    @include('modulo.partials.form')
</form>

{{-- edit.blade.php --}}
<form method="POST" action="{{ route('modulo.update', $registro) }}">
    @csrf @method('PUT')
    @include('modulo.partials.form')
</form>
```

### Flash messages con SweetAlert2
Los controllers redirigen con `->with('success', 'MENSAJE')` o `->with('error', 'MENSAJE')`. El layout `app.blade.php` los captura y muestra como toast:
```blade
@if(session('success'))
<script>
Swal.fire({ toast: true, position: 'top-end', icon: 'success',
    title: '{{ session("success") }}', showConfirmButton: false,
    timer: 3500, timerProgressBar: true });
</script>
@endif
```

### Confirmación de eliminación
Siempre con SweetAlert2, nunca con `confirm()` nativo. El formulario tiene `id="form-eliminar-{id}"` y el botón dispara el Swal:
```javascript
Swal.fire({ title: '¿ELIMINAR REGISTRO?', icon: 'warning',
    showCancelButton: true, confirmButtonColor: '#dc2626',
    confirmButtonText: 'SÍ, ELIMINAR', cancelButtonText: 'CANCELAR'
}).then(r => { if (r.isConfirmed) document.getElementById('form-eliminar-' + id).submit(); });
```

---

## COLORES DE ESTADO (Tailwind — usar siempre el mapa oficial)

```
PENDIENTE    → bg-yellow-100 text-yellow-800
ACTIVO/ACTIVA → bg-green-100 text-green-800
INACTIVO/INACTIVA → bg-gray-100 text-gray-800
SUSPENDIDO/SUSPENDIDA → bg-red-100 text-red-800
EGRESADO     → bg-blue-100 text-blue-800
VENCIDA      → bg-orange-100 text-orange-800
EN PROCESO   → bg-blue-100 text-blue-800
RESUELTO     → bg-green-100 text-green-800
RECHAZADO    → bg-red-100 text-red-800
ABANDONADO/ABANDONÓ → bg-gray-100 text-gray-800
FINALIZADO   → bg-blue-100 text-blue-800
BORRADOR     → bg-gray-100 text-gray-800
EN REVISIÓN  → bg-yellow-100 text-yellow-800
APROBADA     → bg-blue-100 text-blue-800
PUBLICADA    → bg-green-100 text-green-800
DESACTUALIZADA → bg-orange-100 text-orange-800
ARCHIVADA    → bg-red-100 text-red-800
```

Uso en Blade:
```blade
<span class="px-2 py-1 rounded-full text-xs font-semibold {{ $coloresPorEstado[$registro->est_xxx] ?? 'bg-gray-100 text-gray-800' }}">
    {{ $registro->est_xxx }}
</span>
```

---

## BASE DE DATOS — MIGRACIONES

Las migraciones están prefijadas con `2026_04_18_XXXXXX_`. Orden de ejecución importante:

1. `parametros_sistema` → `roles` → `usuarios` (users)
2. `personas_no_videntes` → `tutores_contactos`
3. `tipos_afiliado` → `afiliaciones` → `federaciones` → `afiliaciones_federaciones`
4. `beneficios_sociales` → `asignacion_beneficios`
5. `instituciones_externas` → `tramites_instituciones` → `accesos_salud`
6. `tipos_capacitacion` → `capacitaciones` → `participaciones_capacitacion`
7. `niveles_seguridad` → `rutas` → `calles_tramos` → `condiciones_entorno` → `obstaculos` → `evaluaciones_riesgo` → `zonas_riesgo`
8. `recorridos_realizados` → `experiencias_usuario` (módulo pendiente)

---

## MÓDULOS PENDIENTES (NO IMPLEMENTADOS AÚN)

| Módulo | Tablas en BD | Estado |
|--------|-------------|--------|
| M3 Federaciones | `federaciones`, `afiliaciones_federaciones` | Migración existe, sin CRUD |
| M8 Recorridos | `recorridos_realizados`, `experiencias_usuario` | Migración existe, sin CRUD |
| M0 Seguridad | `roles`, `parametros_sistema` | Migración existe, sin gestión UI |

---

## COMANDOS FRECUENTES

```bash
# Limpiar caché (siempre después de cambios en config/rutas/vistas)
php artisan config:clear && php artisan route:clear && php artisan view:clear

# Ver todas las rutas registradas
php artisan route:list

# Crear un nuevo controller con todos los métodos resource
php artisan make:controller NuevoModuloController --resource

# Crear Form Requests para un nuevo módulo
php artisan make:request StoreNuevoModuloRequest
php artisan make:request UpdateNuevoModuloRequest

# Correr migraciones
php artisan migrate

# Crear modelo con migración y factory
php artisan make:model NuevoModelo -m
```

---

## CHECKLIST PARA AGREGAR UN NUEVO MÓDULO

Al seguir la estructura del proyecto, un nuevo módulo debe incluir:

- [ ] **Migración:** `php artisan make:migration create_[tabla]_table` con nomenclatura de campos `[abrev]_[tabla]`
- [ ] **Modelo:** `php artisan make:model NuevoModelo` con `$fillable`, relaciones, scopes, campo `fbj_*` para borrado lógico
- [ ] **Form Requests:** `StoreNuevoRequest` y `UpdateNuevoRequest` con mensajes en MAYÚSCULAS
- [ ] **Controller:** Resource controller, todos los métodos, `Auth::id()` en store, soft delete en destroy
- [ ] **Ruta:** `Route::resource()` dentro del grupo `auth` en `web.php`
- [ ] **Vistas:**
  - `[modulo]/index.blade.php`
  - `[modulo]/create.blade.php` (incluye `partials/form`)
  - `[modulo]/edit.blade.php` (incluye `partials/form`)
  - `[modulo]/show.blade.php` (si aplica)
  - `[modulo]/partials/form.blade.php`
  - `[modulo]/partials/tabla.blade.php`
  - `[modulo]/partials/filtros.blade.php`
- [ ] **Enlace en sidebar:** Actualizar `layouts/app.blade.php` con enlace al módulo
- [ ] **Permisos:** Agregar métodos `puede*()` en `User.php` si aplica

---

*Generado: 2026-05-02 — basado en estado actual del repositorio (rama: main)*
