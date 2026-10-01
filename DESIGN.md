---
name: Setlist
description: Herramienta de repertorio y setlists en vivo para bandas y directores de música
colors:
  primary: "#1ed760"
  primary-hover: "#1db954"
  background-light: "#f8fafc"
  background-dark: "#121212"
  card-dark: "#181818"
  card-dark-hover: "#242424"
  text-secondary: "#a7a7a7"
  brand-green: "#1ed760"
  brand-charcoal: "#121212"
  accent: "#1ed760"
  pure-black: "#000000"
  pure-white: "#ffffff"
typography:
  display:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "2.5rem"
    fontWeight: 800
    lineHeight: 1.15
  headline:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.75rem"
    fontWeight: 700
    lineHeight: 1.25
  title:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 600
    lineHeight: 1.4
  body:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.9375rem"
    fontWeight: 400
    lineHeight: 1.5
  label:
    fontFamily: "Inter, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1.4
rounded:
  sm: "4px"
  md: "8px"
  lg: "12px"
  xl: "16px"
  full: "9999px"
spacing:
  xs: "4px"
  sm: "8px"
  md: "16px"
  lg: "24px"
  xl: "32px"
components:
  button-primary:
    backgroundColor: "{colors.primary}"
    textColor: "#000000"
    rounded: "{rounded.full}"
    padding: "12px 28px"
  button-primary-hover:
    backgroundColor: "{colors.primary-hover}"
---

# Design System: Setlist

## Overview

**Creative North Star: "Live Stage (Spotify Edition)"**

Setlist adopta la sobriedad, energía y alta fidelidad de la experiencia musical moderna al estilo Spotify: un lienzo oscuro absoluto (`#121212`), tarjetas de superficie con elevación tonal sutil (`#181818` a `#242424`), tipografía `Inter` en blanco puro y gris accesible (`#a7a7a7`), y el icónico acento verde brillante (`#1ed760`) para llamadas a la acción, estados activos y momentos de interacción en vivo.

Diseñado específicamente para músicos y bandas, el sistema prioriza la rapidez de escaneo táctico: títulos claros, insignias de tono musical en alto contraste, botones redondeados ("pills") con áreas de toque generosas para dispositivos móviles en escenario, y cero ornamentos innecesarios o degradados borrosos ("AI slop").

**Key Characteristics:**
- **Fondo oscuro de alta fidelidad:** Base `#121212` con tarjetas `#181818` y bordes sutiles de 1px (`#282828`).
- **Acento Verde Spotify:** `#1ed760` en botones principales, badges de tono activo y micro-interacciones.
- **Tipografía clara y contrastada:** `Inter` puro, sin fuentes serif decorativas ni tamaños arbitrarios.
- **Botones y Badges redondeados:** Estética de píldora (`rounded-full`) distintiva para llamadas a la acción y metadatos.
- **Flux UI Pro + Tailwind CSS v4:** Estandarización total con los componentes y variables del proyecto.

## Colors

### Primary & Accent (Spotify Green)
- `Primary` (`#1ed760`): Verde vibrante de alta visibilidad para botones principales, estados activos y llamadas a la acción.
- `Primary Hover` (`#1db954`): Tono verde saturado para feedback de hover y click.
- `Accent Content` (`#1ed760`): Texto de acento e iconos clave.

### Neutral Surfaces
- `Background Dark` (`#121212`): Fondo base inmersivo en modo oscuro.
- `Card Dark` (`#181818`): Superficie para tarjetas de canciones, bloques de contenido y paneles.
- `Card Dark Hover` (`#242424`): Elevación tonal para filas de canciones y elementos interactivos.
- `Border Subtle` (`#282828`): Delimitación limpia de contenedores.
- `Text Primary` (`#ffffff`): 100% blanco para títulos y elementos principales.
- `Text Secondary` (`#a7a7a7`): Gris neutro accesible para acordes, autores y subtítulos.

## Typography

- **Display (Hero Headline):** 2.5rem - 3.5rem, bold / extrabold (700-800).
- **Headline (Secciones):** 1.75rem - 2rem, semibold / bold (600-700).
- **Title (Canciones / Items):** 1.125rem (18px), semibold (600).
- **Body:** 0.9375rem (15px), regular (400).
- **Label / Badges:** 0.75rem (12px), semibold / bold (600), tracking limpio.

## Components

- **Botones:**
  - *Primario (Pill):* `bg-[#1ed760] hover:bg-[#1db954] text-black font-bold rounded-full px-6 py-3 transition duration-200 transform active:scale-95`.
  - *Secundario / Outline:* `border border-zinc-700 hover:border-white text-white font-semibold rounded-full px-6 py-3 transition`.
- **Badges de Tono Musical:** Píldoras compactas de alto contraste (`bg-zinc-800 text-white font-mono border border-zinc-700`).
- **Lista de Canciones:** Filas con hover a `#242424`, numeración a la izquierda, título en blanco y tono en verde o gris.

## Do's and Don'ts

### Do:
- Utilizar verde `#1ed760` con texto negro en botones primarios para máximo contraste (WCAG AAA).
- Emplear esquinas redondeadas tipo píldora (`rounded-full`) en botones de acción y tags de estado.
- Mantener la interfaz limpia, rápida y directa para músicos en vivo.

### Don't:
- No usar degradados multicolores brillantes o texto con gradiente.
- No utilizar fuentes serif o fuentes sobreutilizadas que no pertenezcan al sistema tipográfico.
- No usar tamaños de fuente menores a 12px (`text-xs`).
