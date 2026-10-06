<!--
SPDX-FileCopyrightText: 2026 Eduardo Monsalve Ariza
SPDX-FileCopyrightText: 2026 Jesús Manuel Farfán
SPDX-FileCopyrightText: 2026 Ángel Manuel Quintero

SPDX-License-Identifier: Apache-2.0
-->

<!--
-->

# Locus

Sistema de control de asistencias con códigos QR y verificación de ubicación (GPS/Red). Pensado para eventos de cualquier tipo: un organizador crea una sesión, los participantes escanean el QR y se registra su asistencia, con o sin geocerca. Desarrollado en PHP 8.1 sin framework.

## Requisitos

- Podman o Docker

## Inicio rápido

```bash
cd locus
podman compose up -d
```

La app queda en `http://localhost:8080`.

### Acceder desde el celular

Podman rootless publica los puertos en todas las interfaces, sin necesidad de `sudo`. Solo hay que entrar por la IP local en vez de `localhost`, porque en el celular `localhost` apunta al propio teléfono:

```bash
ip route get 1.1.1.1 | grep -oP 'src \K[\d.]+'
```

Abre esa IP en el celular con el mismo WiFi (ej. `http://192.168.1.17:8080`). Los códigos QR que generes contendrán esa IP y se escanearán desde el teléfono sin problema.

Si el celular no conecta, casi siempre es que está en una red de invitados con aislamiento de clientes activado, que bloquea el tráfico entre dispositivos.

### Usuarios de prueba (contraseña: `123456`)

| Email | Rol |
|-------|-----|
| y.corrales@locus-demo.test | Organizador |
| w.sanclemente@locus-demo.test | Participante |
| k.penarreta@locus-demo.test | Participante |

## Ubicaciones de registro

Estas ubicaciones son reales, pero están a unos 260 km de Bogotá: el mapa se abrirá ahí, no en la capital. Para probar con otros puntos, créalos desde la app en **Panel → Ubicaciones → clic en el mapa**.

| Ubicación | Coordenadas | WiFi |
|-----------|-------------|------|
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
