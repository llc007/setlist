# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
Músicos, directores de alabanza y líderes de bandas musicales que necesitan organizar y consultar su repertorio de canciones, gestionar integrantes por banda y armar setlists para ensayos y presentaciones en vivo. Como audiencia secundaria: los integrantes de la banda y músicos invitados que acceden al setlist compartido desde sus teléfonos móviles.

## Product Purpose
Proporcionar una plataforma centralizada y eficiente para gestionar repertorios de canciones, asignar recursos (letras, acordes, enlaces, audios, PDFs) y preparar setlists ordenados para eventos y presentaciones, reduciendo la fricción y el desorden previo y durante el toque en vivo.

## Positioning
Una herramienta ágil y enfocada para bandas, diseñada para operar en tiempo real: acceso inmediato a las canciones de un evento, visualización limpia optimizada para pantallas móviles en escenario o ensayo, y la capacidad de compartir el setlist público mediante enlaces de WhatsApp sin exigir que los músicos inicien sesión.

## Operating Context
- Uso activo durante ensayos y conciertos/servicios en vivo, a menudo sobre atriles o soportes con teléfonos o tablets.
- Iluminación variable (escenarios oscuros o salas iluminadas), lo que hace crítico el soporte completo de modo oscuro/claro y alta legibilidad.
- Conectividad a veces inestable en recintos de eventos: las vistas de consulta en vivo deben ser ligeras y directas.

## Capabilities and Constraints
- Gestión multiequipo/bandas con roles y permisos específicos (administración, miembros, propietarios de banda).
- Catálogo de canciones con filtros por categorías, códigos internos, archivos adjuntos (PDFs) y recursos asociados.
- Creación, ordenamiento y personalización de setlists por banda y fecha/evento.
- Vista pública compartible del Setlist (`/setlist/{setlist}`) optimizada para lectura en vivo sin requerir autenticación.
- Stack tecnológico: Laravel 12, Livewire 4, Flux UI Pro, Tailwind CSS v4.

## Brand Commitments
- Nombre: Setlist.
- Identidad visual limpia, utilitaria y moderna, priorizando la claridad y la legibilidad sobre adornos innecesarios.
- Experiencia de usuario rápida con soporte para modo oscuro (`dark:`) y componentes nativos de Flux UI Pro.

## Evidence on Hand
- Modelos existentes: `Banda`, `Cancion`, `CancionRecurso`, `Categoria`, `Setlist`, `User`.
- Rutas públicas y protegidas en `routes/web.php`.
- Vistas Blade y componentes Livewire bajo `resources/views/`.

## Product Principles
1. **Claridad en el escenario**: La información esencial (título, tono, orden, notas de canción) debe leerse de un vistazo a distancia de atril.
2. **Fricción cero para compartir**: Cualquier músico debe poder abrir y consultar el setlist del día desde WhatsApp con un solo clic.
3. **Consistencia técnica y visual**: Aprovechar los componentes de Flux UI Pro y Tailwind v4 para mantener coherencia en todo el ecosistema.
4. **Respeto a las condiciones de luz**: Contraste optimizado tanto en modo oscuro para escenarios como en modo claro para ensayos diurnos.

## Accessibility & Inclusion
- Contraste legible según estándares WCAG AA, especialmente en etiquetas de tonalidad y acordes.
- Interacciones táctiles cómodas en pantallas táctiles (botones y tarjetas con áreas de toque amplias para músicos usando tablets/teléfonos).
