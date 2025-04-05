@echo off
echo Iniciando proceso de ofuscación...

set SOURCES=includes config
set TARGET_BASE=dist

EM Crear directorios de destino si no existen
if not exist %TARGET_BASE% mkdir %TARGET_BASE%

REM Copiar archivos principales y carpetas necesarias
echo Copiando archivos que no requieren ofuscación...
copy fronpe-settings.php %TARGET_BASE%\fronpe-settings.php /Y
copy index.php %TARGET_BASE%\index.php /Y
copy README.md %TARGET_BASE%\README.md /Y
copy LICENSE.txt %TARGET_BASE%\LICENSE.txt /Y

REM Copiar la carpeta vendor
echo Copiando dependencias de Composer...
if not exist %TARGET_BASE%\vendor mkdir %TARGET_BASE%\vendor
xcopy vendor %TARGET_BASE%\vendor /E /I /Y

REM Copiar otras carpetas si es necesario
if not exist %TARGET_BASE%\assets mkdir %TARGET_BASE%\assets
xcopy assets %TARGET_BASE%\assets /E /I /Y
if not exist %TARGET_BASE%\languages mkdir %TARGET_BASE%\languages
xcopy languages %TARGET_BASE%\languages /E /I /Y

REM Luego realizar la ofuscación
for %%s in (%SOURCES%) do (
    echo Ofuscando %%s...
    php obfuscate\yakpro-po\yakpro-po.php --config-file yakpro-po.cnf %%s -o %TARGET_BASE%\%%s
)

echo Proceso de ofuscación completado.
