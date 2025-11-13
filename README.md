GUÍA DE INSTALACIÓN / DESPLIEGUE DEL SISTEMA “Mini Party Reviews”
1. Requisitos previos
Antes de instalar el sistema, asegúrate de tener lo siguiente:
•	Servidor local o remoto con soporte PHP y MySQL/MariaDB (por ejemplo, XAMPP, WAMP o Laragon).
•	PHP 7.4 o superior.
•	Servidor Apache habilitado.
•	Navegador web actualizado (Chrome, Edge, Firefox, etc.).
•	Base de datos importada (archivo .sql correspondiente al sistema).
2. Instalación paso a paso
1.	Descargar el proyecto
2.	Configurar la base de datos
       CREATE DATABASE mini_party_reviews;
3.	Configurar la conexión a la base de datos
Abre el archivo conexion.php.
Edita los valores de conexión según tu entorno local:
Guarda los cambios.
4.	Iniciar el servidor local:
Abre el panel de control de XAMPP 
Inicia Apache y MySQL.
5.	Abrir el sistema en el navegador:
Ingresa en la barra de direcciones:
http://localhost/mini_party_reviews
Accede con un usuario administrador registrado (o crea uno en la base de datos si es la primera vez).
6.	Acceder al panel de administración:
Una vez logueado como admin, serás redirigido a:
http://localhost/mini_party_reviews/admin/admin_index.php
Allí podrás gestionar usuarios, salones, comentarios, zonas y fotos eso solo si se es administrador en caso de no serlo directamente se irán a ver los salones.
