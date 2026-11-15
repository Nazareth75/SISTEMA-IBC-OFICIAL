# CARPETA DE EVIDENCIAS TÉCNICAS PARA EL MANUAL DE INSTALACIÓN
**Proyecto:** SISTEMA-IBC-OFICIAL (Instituto Boliviano de la Ceguera)  
**Ubicación:** `C:\laragon\www\SISTEMA-IBC-OFICIAL\EVIDENCIAS_MANUAL`

---

## Contenido de la Carpeta

Esta carpeta reúne tanto las figuras gráficas de análisis del sistema como las evidencias textuales de ejecución extraídas directamente del entorno real de desarrollo y base de datos:

1. **`01_grafo_topologico.png`**  
   - *Tipo:* Imagen PNG de alta resolución.  
   - *Descripción:* Representación visual de la red vial peatonal modelada como grafo matemático (nodos de intersección y aristas/tramos viarios).  
   - *Uso en el manual:* Capítulo de Arquitectura y Algoritmo de Movilidad Asistida (ACO).

2. **`02_convergencia_comparativa.png`**  
   - *Tipo:* Imagen PNG de alta resolución.  
   - *Descripción:* Curvas de convergencia del Algoritmo de Colonia de Hormigas (ACO) comparando iteraciones contra costo de función objetivo y distancia en metros.  
   - *Uso en el manual:* Capítulo de Validación Experimental y Optimización Heurística.

3. **`03_evolucion_feromonas.png`**  
   - *Tipo:* Imagen PNG de alta resolución.  
   - *Descripción:* Evolución temporal del depósito y evaporación de feromonas sobre el conjunto de caminos alternativos.  
   - *Uso en el manual:* Explicación matemática de la dinámica bioinspirada.

4. **`04_grafo_feromonas_finales.png`**  
   - *Tipo:* Imagen PNG de alta resolución.  
   - *Descripción:* Topología final del grafo con concentración máxima de feromonas sobre el trayecto óptimo seguro para personas no videntes.  
   - *Uso en el manual:* Demostración de selección de ruta segura.

5. **`05_comparacion_metricas_m1_m8.png`**  
   - *Tipo:* Imagen PNG de alta resolución.  
   - *Descripción:* Gráfica de barras comparativa de métricas multiobjetivo M1 a M8 (balance de riesgo vial, distancia métrica y costo total).  
   - *Uso en el manual:* Evaluación de rendimiento y resultados del sistema.

6. **`06_evidencia_migraciones_ejecutadas.txt`**  
   - *Tipo:* Salida de consola oficial (`php artisan migrate:status`).  
   - *Descripción:* Registro verificado de las 41 migraciones de base de datos creadas y ejecutadas en MySQL por lotes.  
   - *Uso en el manual:* Anexo o evidencia de creación de esquema de base de datos.

7. **`07_evidencia_rutas_registradas.txt`**  
   - *Tipo:* Salida de consola oficial (`php artisan route:list`).  
   - *Descripción:* Catálogo detallado de todas las rutas HTTP del sistema (controladores web administrativos, APIs AJAX y endpoints móviles).  
   - *Uso en el manual:* Capítulo de Routing y Endpoints de API.

8. **`08_evidencia_tablas_mysql.txt`**  
   - *Tipo:* Consulta SQL (`SHOW TABLES`).  
   - *Descripción:* Listado verificado de las 40 tablas creadas en la base de datos `sistema_ibc_oficial`.  
   - *Uso en el manual:* Capítulo de Base de Datos.

9. **`09_evidencia_pruebas_unitarias.txt`**  
   - *Tipo:* Salida de consola oficial (`php artisan test --testsuite=Unit`).  
   - *Descripción:* Reporte completo de las 206 pruebas unitarias ejecutadas con éxito (1,268 aserciones sin fallos).  
   - *Uso en el manual:* Capítulo de Control de Calidad y Pruebas del Sistema.

