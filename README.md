<!--
SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero

SPDX-License-Identifier: Apache-2.0
-->

<!--
-->

# Locus

Sistema de gestión de asistencias con códigos QR y verificación de ubicación (GPS/Red). Desarrollado en PHP 8.1 sin framework.

## Requisitos

- Podman o Docker

## Inicio rápido

```bash
cd locus
podman compose up -d
```

La app queda en `http://localhost:8080`.

### Acceder desde otros dispositivos en la misma red

Por defecto, Podman rootless publica puertos solo en `127.0.0.1`, por lo que el servidor no es accesible desde otros dispositivos (ej. celular). Para hacer pruebas en la misma red, ejecuta Podman como root:

```bash
sudo podman compose up -d
```

Luego busca la IP local del servidor:

```bash
ip addr show | grep 'inet ' | grep -v 127.0.0.1
```

Accede desde el PC usando la IP local (ej. `http://192.168.1.7:8080`) — así los códigos QR contendrán esa IP y podrán escanearse desde el celular en la misma red WiFi.

### Usuarios de prueba (password: `123456`)

| Email | Rol | Semillero |
|-------|-----|-----------|
| angely@udi.edu.co | Docente | Psicología |
| alzate@udi.edu.co | Docente | Ingeniería de Sistemas |
| angel@udi.edu.co | Estudiante | Ingeniería de Sistemas |
| farfan@udi.edu.co | Estudiante | Ingeniería de Sistemas |
| monsalve@udi.edu.co | Estudiante | Ingeniería de Sistemas |
| botero@udi.edu.co | Estudiante | Ingeniería de Sistemas |
| malo@udi.edu.co | Estudiante | Psicología |
| valeria@udi.edu.co | Estudiante | Psicología |

## Sedes

| Sede | Coordenadas | WiFi |
|------|-------------|------|
| Ana Frank | 7.0587899, -73.8626501 | WBAF-estudiantes |
| Marie Curie | 7.0623784, -73.8580640 | WBcaMC-estudiantes |

## Comandos útiles

```bash
podman compose down          # Detener
podman compose down -v       # Detener y borrar datos
```

## Tests

```bash
composer test
```
