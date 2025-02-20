# Sistema de Anexos - Municipalidad de Melipilla

## Descripción del Proyecto
Este sistema está desarrollado utilizando las siguientes tecnologías:
* **PHP**
* **JavaScript**
* **Composer** (para la gestión de dependencias)

El sistema permite la gestión de anexos y está diseñado para ser utilizado por el personal autorizado de la Municipalidad de Melipilla.

## 📝 Instrucciones para Configurar el Proyecto

### 1. Acceso al Sistema
* **URL del Sistema**: https://172.16.0.26:2031
* **Credenciales de Acceso**:
   * **Usuario**: `PREGUNTAR_ADMINISTRACION`
   * **Contraseña**: `PREGUNTAR_ADMINISTRACION`

**Nota**: Para obtener acceso al sistema, debes generar un ticket y solicitar las credenciales al administrador.

### 2. Panel de Administración (CWP Control Panel)
* Una vez que hayas ingresado al sistema, serás redirigido al panel de administración de **CWP Control Panel**.

### 3. Instalación de Composer
* **Composer** es una herramienta de gestión de dependencias para PHP. Para instalarlo, sigue estos pasos:
   1. Accede a la terminal de tu panel de control.
   2. Ejecuta el siguiente comando para instalar Composer:
```bash
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
```
   3. Verifica la instalación ejecutando:
```bash
composer --version
```

### 4. Base de Datos
* **Nombre de la Base de Datos**: `intranet_informa`
* **Tablas Principales**: `usuarios` y `acces`
* **Acceso a la Base de Datos**:
   * **URL**: https://172.16.0.26:2083
   * **Credenciales**:
      * **Usuario**: `intranet`
      * **Contraseña**: `yGpsS5gpypne` (¡Asegúrate de mantener esta información segura!)

### 5. Estructura de Archivos
* Para visualizar y modificar los archivos del sistema, sigue estos pasos:
   1. Accede a **File Manager** desde el panel de administración.
   2. Navega a la siguiente ruta:
```
home -> intranet -> public_html -> informatica -> sistema-anexos
```
   3. **Carpetas Importantes**:
      * `config`: Contiene la configuración de la base de datos.
      * `views`: Aquí se encuentran las vistas y funciones del sistema.

### 6. Modificaciones en WordPress
* Para realizar cambios en el sitio de WordPress, sigue estos pasos:
   1. Accede al panel de administración de WordPress:
      * **URL**: https://intranet.municipalidad.cl/informatica/wp-admin
   2. Ingresa con las credenciales proporcionadas por el administrador.
   3. En la barra lateral izquierda, busca el plugin **CSS y JS Personalizado** para modificar el diseño y funcionalidad del sistema de anexos.

## ⚠️ Notas Importantes
* **Seguridad**: Asegúrate de no compartir las credenciales de acceso públicamente. Utiliza contraseñas seguras y cámbialas periódicamente.
* **Backups**: Realiza copias de seguridad regulares de la base de datos y los archivos del sistema.

## 📄 Licencia
Este proyecto está bajo la licencia **MIT**. Para más detalles, consulta el archivo LICENSE.

## 🤝 Contribuciones
Si deseas contribuir al proyecto, por favor sigue estos pasos:
1. Haz un fork del repositorio.
2. Crea una rama para tu contribución (`git checkout -b feature/nueva-funcionalidad`).
3. Realiza tus cambios y haz commit (`git commit -m 'Añade nueva funcionalidad'`).
4. Haz push a la rama (`git push origin feature/nueva-funcionalidad`).
5. Abre un Pull Request.

## 📧 Contacto
Si tienes alguna duda o necesitas asistencia, por favor contacta al administrador del sistema.
