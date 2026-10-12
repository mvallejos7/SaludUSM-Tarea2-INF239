TAREA 2 - INF239 BASES DE DATOS
SaludUSM

Integrantes:
- Diego Alarcón - Rol: 202430509-3
- Matias Vallejos - Rol: 202330505-7

DBMS utilizado:
MySQL

Lenguaje utilizado:
PHP

Base de datos:
salud_usm

Repositorio GitHub:
https://github.com/mvallejos7/SaludUSM-Tarea2-INF239


INSTRUCCIONES DE EJECUCION

1. Tener instalado MySQL y PHP.

2. Verificar que PHP tenga habilitadas las extensiones PDO y PDO MySQL.

3. Abrir PowerShell o una terminal.

4. Iniciar MySQL utilizando un usuario con permisos para crear bases de datos:

   mysql --default-character-set=utf8mb4 -u root -p

5. Ejecutar el archivo:

   BD/salud_usm.sql

   Desde el cliente MySQL se puede ejecutar con:

   SOURCE C:/SaludUSM/BD/salud_usm.sql;

   Este script elimina la base de datos salud_usm si ya existe y luego la crea nuevamente.

   El archivo incluye:
   - Tablas.
   - Claves primarias y foraneas.
   - Restricciones.
   - Datos de prueba.
   - View.
   - Function.
   - Procedure.
   - Trigger.
   - Usuario MySQL utilizado por la aplicacion PHP.

6. El usuario MySQL utilizado por PHP es:

   Usuario:
   salud_app

   Contraseña:
   SaludUSM_DB_2026!

7. Abrir otra ventana de PowerShell y ubicarse en la carpeta del proyecto:

   cd C:\SaludUSM

8. Iniciar el servidor PHP:

   php -S localhost:8000 -t PHP

9. Abrir la aplicacion desde el navegador ingresando a:

   localhost:8000

10. Iniciar sesion utilizando alguno de los usuarios de prueba indicados a continuacion.


USUARIOS DE PRUEBA

Paciente:

Correo:
paciente01@saludusm.cl

Contraseña:
SaludUSM2026!


Medico:

Correo:
medico01@saludusm.cl

Contraseña:
SaludUSM2026!

Este medico posee mas de una especialidad y atiende en mas de un centro.


Administrador:

Correo:
admin@saludusm.cl

Contraseña:
SaludUSM2026!


REQUISITOS DE REGISTRO

El registro desde la pagina de inicio esta disponible solamente para pacientes.

El RUT debe utilizar el formato:

XXXXXXXX-X

El RUT y el correo electronico deben ser unicos.

La contraseña debe cumplir los siguientes requisitos:
- Minimo 8 caracteres.
- Al menos una letra mayuscula.
- Al menos una letra minuscula.
- Al menos un numero.


DESCRIPCION DE LOS ROLES

Paciente:

El paciente puede registrarse, modificar su perfil, eliminar su cuenta, buscar medicos, agendar citas, revisar sus citas, reprogramarlas, cancelarlas y consultar su historial medico y recetas.


Medico:

El medico puede modificar su perfil, revisar su agenda por dia, cambiar el estado de sus citas, registrar atenciones, diagnosticos y recetas y consultar el historial clinico de sus pacientes.


Administrador:

El administrador puede modificar su perfil, administrar centros medicos, especialidades y medicos, asignar especialidades y centros a los medicos y consultar las estadisticas de SaludUSM.


AGENDAMIENTO DE CITAS

Los bloques de atencion tienen una duracion de 30 minutos.

El horario considerado es desde las 08:00 hasta las 18:00 horas.

El ultimo bloque comienza a las 17:30 horas.

Se consideran dias de atencion de lunes a viernes.

No se consideran dias feriados ni cierres especiales.

No existe una tabla independiente para almacenar la disponibilidad de cada medico.

Se considera que un medico se encuentra disponible dentro del horario definido si atiende en el centro seleccionado y no posee otra cita activa en ese horario.


ESTADOS Y DISPONIBILIDAD DE LAS CITAS

Los estados de las citas son:

- Reservada.
- Confirmada.
- Atendida.
- No Asistio.
- Cancelada.

Solamente las citas Reservadas y Confirmadas bloquean un horario.

Las citas Atendidas, No Asistio y Canceladas se conservan como informacion historica, pero no bloquean el horario para una nueva reserva.

El sistema impide que un medico tenga dos citas activas en el mismo horario.

El sistema tambien impide que un paciente tenga dos citas activas en el mismo horario.

Una cita solo puede realizarse con una especialidad que posea el medico y en un centro donde dicho medico atienda.


REPROGRAMACION Y CANCELACION DE CITAS

Un paciente solamente puede reprogramar o cancelar sus propias citas.

La cita debe encontrarse en estado Reservada o Confirmada y su fecha no debe haber pasado.

Al reprogramar una cita se modifica solamente la fecha y hora, manteniendo el medico, especialidad y centro original.


CITAS VENCIDAS

Las citas Reservadas o Confirmadas cuya fecha y hora ya pasaron deben cambiar a estado Cancelada.

Para realizar esta actualizacion se utiliza el procedimiento almacenado:

sp_cancelar_citas_vencidas

El procedimiento se ejecuta desde la aplicacion antes de mostrar informacion en secciones que trabajan con citas, como Mis citas, Agenda, Busqueda avanzada de citas y Estadisticas.


ELIMINACION DE CUENTA DEL PACIENTE

La eliminacion de la cuenta del paciente es logica.

Al eliminar la cuenta:
- El usuario queda inactivo.
- El paciente ya no puede iniciar sesion.
- Sus citas futuras Reservadas o Confirmadas pasan a Cancelada.
- Las citas historicas, atenciones, diagnosticos y recetas se mantienen.

Esto permite conservar el historial clinico.


MEDICOS, ESPECIALIDADES Y CENTROS

Cada medico debe tener entre 1 y 3 especialidades.

Cada medico debe atender en al menos un centro.

Si se intenta quitar una especialidad o centro que posee citas futuras Reservadas o Confirmadas asociadas, el sistema impide realizar el cambio.

Tampoco se permite desactivar un medico si tiene citas futuras Reservadas o Confirmadas.


ATENCIONES

Una atencion solamente puede registrarse si la cita pertenece al medico conectado y se encuentra en estado Atendida.

Cada cita puede tener como maximo una atencion.

La validacion tambien es realizada mediante el Trigger:

trg_validar_atencion


DIAGNOSTICOS

Una atencion puede tener uno o mas diagnosticos asociados.

La aplicacion permite agregar, editar y quitar diagnosticos. Una vez que una atencion posee diagnosticos asociados, no se permite eliminar el ultimo diagnostico.

Los diagnosticos utilizan codigo CIE-10 y descripcion.

La relacion entre las atenciones y los diagnosticos se almacena mediante la tabla:

atencion_diagnostico


RECETAS

Las recetas se encuentran asociadas a una atencion.

Cada linea de receta contiene:
- Medicamento.
- Dosis.
- Dias de tratamiento.

No pueden existir recetas sin una atencion asociada.


HISTORIAL CLINICO

El paciente puede consultar su propio historial clinico, pero no modificarlo.

El medico puede consultar el historial de pacientes que haya atendido previamente.

El historial muestra:
- Fecha.
- Especialidad.
- Medico.
- Centro.
- Motivo de consulta.
- Observaciones.
- Diagnosticos.
- Recetas.


BUSQUEDA DE MEDICOS

Todos los roles pueden utilizar la busqueda de medicos.

La barra de busqueda permite buscar utilizando un fragmento del nombre del medico o de una de sus especialidades.

Adicionalmente, es posible combinar filtros por:
- Especialidad.
- Centro medico.
- Comuna.

Solamente se consideran medicos y asociaciones activas.

BUSQUEDA AVANZADA DE CITAS

La busqueda avanzada permite utilizar filtros combinables por:
- Rango de fechas.
- Centro medico.
- Region.
- Especialidad.
- Medico.
- Estado de la cita.
- Prevision del paciente.

El paciente solamente puede visualizar sus propias citas.

El medico solamente puede visualizar sus propias citas.

El administrador puede visualizar todas las citas.


ESTADISTICAS

El administrador puede consultar estadisticas de los centros medicos.

Se muestra:
- Cantidad de citas.
- Estado de las citas.
- Porcentaje de inasistencia.
- Cinco diagnosticos mas frecuentes.

Para calcular el porcentaje de inasistencia se utiliza la Function:

fn_porcentaje_inasistencia


OBJETOS SQL

View:

vw_citas_detalle

Permite consultar el detalle de las citas junto con los datos del paciente, medico, especialidad, centro, prevision y estado.


Function:

fn_porcentaje_inasistencia

Calcula el porcentaje de citas en estado No Asistio correspondiente a un centro medico.


Procedure:

sp_cancelar_citas_vencidas

Actualiza a Cancelada las citas Reservadas o Confirmadas cuya fecha y hora ya pasaron.


Trigger:

trg_validar_atencion

Impide registrar una atencion si la cita correspondiente no se encuentra en estado Atendida.


DATOS DE PRUEBA

El archivo BD/salud_usm.sql incluye los datos necesarios para probar las funcionalidades de la aplicacion.

Entre los datos incluidos se encuentran:
- 3 previsiones.
- 5 estados de cita.
- 8 especialidades.
- 4 centros medicos.
- 40 pacientes.
- 15 medicos.
- Medicos con mas de una especialidad.
- Medicos que atienden en mas de un centro.
- Citas en distintos estados.
- Citas futuras.
- Atenciones.
- Diagnosticos.
- Recetas.


SUPUESTOS

1. Los centros medicos funcionan de lunes a viernes.

2. El horario utilizado para las citas es de 08:00 a 18:00 horas.

3. Cada bloque de atencion tiene una duracion de 30 minutos.

4. No se consideran dias feriados ni cierres especiales de los centros.

5. Un medico asociado a un centro se considera disponible dentro del horario establecido mientras no posea otra cita activa en ese bloque.

6. Reservada y Confirmada son consideradas citas activas.

7. Atendida, No Asistio y Cancelada son consideradas citas historicas y no bloquean nuevos agendamientos.

8. La eliminacion de la cuenta de un paciente es logica para conservar su historial clinico.

9. La desactivacion de medicos se realiza de forma logica para conservar la informacion historica.

10. Los datos contenidos en la base de datos son ficticios y fueron creados para probar el funcionamiento del sistema.

11. Se utilizan sesiones de PHP para identificar al usuario conectado y restringir el acceso de acuerdo con su rol.


ESTRUCTURA DE LA ENTREGA

BD:
Contiene el archivo salud_usm.sql con la base de datos completa.

PHP:
Contiene los archivos de la aplicacion web.

CSS:
Contiene los archivos de estilos de la aplicacion.

JS:
Contiene los archivos JavaScript utilizados por la aplicacion.

PDF:
Contendra el documento con el proceso de normalizacion hasta 3FN.

README.md:
Contiene las instrucciones y consideraciones necesarias para ejecutar y probar el proyecto.