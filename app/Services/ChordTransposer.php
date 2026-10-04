<?php

namespace App\Services;

class ChordTransposer
{
    /**
     * @var string[]
     */
    private const array ESCALAS_SOSTENIDOS = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];

    /**
     * @var string[]
     */
    private const array ESCALAS_BEMOLES = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];

    /**
     * Calcula la diferencia de semitonos entre dos tonalidades (-6 a +6).
     */
    public static function semitonosEntre(string $desde, string $hasta): int
    {
        $raizDesde = self::extraerNotaRaiz($desde);
        $raizHasta = self::extraerNotaRaiz($hasta);

        $posDesde = self::posicionNota($raizDesde);
        $posHasta = self::posicionNota($raizHasta);

        if ($posDesde === null || $posHasta === null) {
            return 0;
        }

        $diff = $posHasta - $posDesde;

        if ($diff > 6) {
            $diff -= 12;
        } elseif ($diff < -6) {
            $diff += 12;
        }

        return $diff;
    }

    /**
     * Extrae la nota raíz de un acorde (ej: "F#m7" -> "F#", "Bb" -> "Bb").
     */
    public static function extraerNotaRaiz(string $acorde): string
    {
        if (preg_match('/^([A-G][b#]?)/i', trim($acorde), $match)) {
            $nota = strtoupper(substr($match[1], 0, 1));
            if (strlen($match[1]) > 1) {
                $alteracion = substr($match[1], 1, 1);
                $nota .= ($alteracion === 'b' ? 'b' : '#');
            }

            return $nota;
        }

        return $acorde;
    }

    /**
     * Obtiene la posición cromática de una nota (0-11).
     */
    public static function posicionNota(string $nota): ?int
    {
        $pos = array_search($nota, self::ESCALAS_SOSTENIDOS, true);
        if ($pos !== false) {
            return $pos;
        }

        $pos = array_search($nota, self::ESCALAS_BEMOLES, true);
        if ($pos !== false) {
            return $pos;
        }

        return null;
    }

    /**
     * Desplaza una nota individual N semitonos.
     */
    public static function shiftNota(string $nota, int $semitonos, bool $preferirBemoles = false): string
    {
        $pos = self::posicionNota($nota);
        if ($pos === null) {
            return $nota;
        }

        $nuevaPos = ($pos + $semitonos) % 12;
        if ($nuevaPos < 0) {
            $nuevaPos += 12;
        }

        return $preferirBemoles
            ? self::ESCALAS_BEMOLES[$nuevaPos]
            : self::ESCALAS_SOSTENIDOS[$nuevaPos];
    }

    /**
     * Transpone un acorde completo incluyendo sufijos y bajos compuestos (ej: G/B -> F/A).
     */
    public static function transponerAcorde(string $acorde, int $semitonos, bool $preferirBemoles = false): string
    {
        if ($semitonos === 0 || empty($acorde)) {
            return $acorde;
        }

        return (string) preg_replace_callback('/([A-G][b#]?)(.*)/', function ($match) use ($semitonos, $preferirBemoles) {
            $nota = $match[1];
            $resto = $match[2];

            if (str_contains($resto, '/')) {
                [$sufijo, $bajo] = explode('/', $resto, 2);
                $notaTrans = self::shiftNota($nota, $semitonos, $preferirBemoles);
                $bajoTrans = self::shiftNota($bajo, $semitonos, $preferirBemoles);

                return $notaTrans.$sufijo.'/'.$bajoTrans;
            }

            return self::shiftNota($nota, $semitonos, $preferirBemoles).$resto;
        }, $acorde);
    }

    /**
     * Determina si un token de texto corresponde a un acorde musical.
     */
    public static function isAcordeToken(string $token): bool
    {
        $token = trim($token);
        if (empty($token) || in_array($token, ['//', '/', '||', '|', '%'], true)) {
            return true;
        }

        return (bool) preg_match('/^[A-G][b#]?(m|maj|min|dim|aug|sus[24]?|add[0-9]+|[0-9]+|b[0-9]+|#[0-9]+|\+|\*|°|ø|-)*(\/[A-G][b#]?)?$/i', $token);
    }

    /**
     * Determina si una línea completa está compuesta predominantemente por acordes.
     */
    public static function isLineaDeAcordes(string $linea): bool
    {
        $palabras = array_values(array_filter(explode(' ', trim($linea))));
        if (empty($palabras)) {
            return false;
        }

        $coincidencias = 0;
        foreach ($palabras as $p) {
            if (self::isAcordeToken($p)) {
                $coincidencias++;
            }
        }

        return ($coincidencias / count($palabras)) >= 0.7;
    }

    /**
     * Transpone el texto completo de una canción (líneas de acordes y [Acordes] entre corchetes).
     */
    public static function transponerTexto(string $texto, int $semitonos, bool $preferirBemoles = false): string
    {
        if ($semitonos === 0 || empty($texto)) {
            return $texto;
        }

        $lineas = explode("\n", $texto);
        $resultado = [];

        foreach ($lineas as $lineaOriginal) {
            $linea = rtrim($lineaOriginal, "\r\n");

            if (empty(trim($linea))) {
                $resultado[] = $lineaOriginal;

                continue;
            }

            // Encabezados de sección tipo [Intro] o [Intro] G D Em (el contenido no es un acorde)
            if (preg_match('/^\s*\[([^\]]+)\]\s*(.*)$/', $linea, $m) && ! self::isAcordeToken($m[1])) {
                $header = '['.$m[1].']';
                $resto = $m[2];
                if (! empty(trim($resto))) {
                    $tokens = preg_split('/(\s+)/', $resto, -1, PREG_SPLIT_DELIM_CAPTURE);
                    $restoTrans = '';
                    foreach ($tokens as $token) {
                        if (trim($token) === '' || ! self::isAcordeToken($token)) {
                            $restoTrans .= $token;
                        } else {
                            $restoTrans .= self::transponerAcorde($token, $semitonos, $preferirBemoles);
                        }
                    }
                    $resultado[] = $header.' '.ltrim($restoTrans);
                } else {
                    $resultado[] = $lineaOriginal;
                }

                continue;
            }

            // Línea de acordes estilo Cifra Club sobre la letra
            if (self::isLineaDeAcordes($linea)) {
                $tokens = preg_split('/(\s+)/', $linea, -1, PREG_SPLIT_DELIM_CAPTURE);
                $lineaTrans = '';
                foreach ($tokens as $token) {
                    if (trim($token) === '' || ! self::isAcordeToken($token)) {
                        $lineaTrans .= $token;
                    } else {
                        $lineaTrans .= self::transponerAcorde($token, $semitonos, $preferirBemoles);
                    }
                }
                $resultado[] = $lineaTrans;

                continue;
            }

            // Letra con acordes entre corchetes tipo [G]Sé que mi [D]Redentor
            if (str_contains($linea, '[')) {
                $lineaTrans = preg_replace_callback('/\[([^\]]+)\]/', function ($match) use ($semitonos, $preferirBemoles) {
                    $contenido = $match[1];
                    if (self::isAcordeToken($contenido)) {
                        return '['.self::transponerAcorde($contenido, $semitonos, $preferirBemoles).']';
                    }

                    return $match[0];
                }, $linea);
                $resultado[] = $lineaTrans;

                continue;
            }

            // Línea de letra normal
            $resultado[] = $lineaOriginal;
        }

        return implode("\n", $resultado);
    }

    /**
     * Determina si una línea es un encabezado de sección musical (ej. [Coro], [Estrofa 2], Coro:, etc.).
     */
    public static function isSectionHeader(string $linea): bool
    {
        $linea = trim($linea);
        if ($linea === '') {
            return false;
        }

        // Encabezados en corchetes: [Intro], [Estrofa 1], [Coro], [Puente], etc.
        if (preg_match('/^\s*\[([^\]]+)\]/i', $linea, $m)) {
            return ! self::isAcordeToken($m[1]);
        }

        $secciones = 'intro|estrofa|verso|verse|coro|chorus|puente|bridge|pre-?coro|pre-?chorus|solo|outro|final|coda|interludio|interlude|instrumental';

        // Sin corchetes con dos puntos: "Coro:", "Estrofa 1:", "Puente:"
        if (preg_match('/^\s*('.$secciones.')(\s+\d+)?\s*:/i', $linea)) {
            return true;
        }

        // Línea aislada que sólo contiene el nombre de la sección: "Coro", "Estrofa 2"
        if (preg_match('/^\s*('.$secciones.')(\s+\d+)?\s*$/i', $linea)) {
            return true;
        }

        return false;
    }

    /**
     * Divide las líneas de una canción en dos columnas equilibradas de forma inteligente.
     * La columna 2 siempre comenzará con el inicio de una sección (ej. [Coro], [Estrofa 2], etc.)
     * cuando existan secciones identificables en la canción.
     *
     * @param  array<int, string>  $lineas
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    public static function dividirLineasEnDosColumnas(array $lineas): array
    {
        $totalLineas = count($lineas);
        if ($totalLineas <= 1) {
            return [$lineas, []];
        }

        // 1. Agrupar las líneas en bloques/secciones
        $secciones = [];
        $seccionActual = [
            'esHeader' => false,
            'header' => null,
            'lineas' => [],
        ];

        foreach ($lineas as $lineaOriginal) {
            $linea = rtrim($lineaOriginal, "\r\n");
            $esHeader = self::isSectionHeader($linea);

            if ($esHeader && (! empty($seccionActual['lineas']) || $seccionActual['esHeader'])) {
                $secciones[] = $seccionActual;
                $seccionActual = [
                    'esHeader' => true,
                    'header' => trim($linea),
                    'lineas' => [$lineaOriginal],
                ];
            } else {
                if ($esHeader) {
                    $seccionActual['esHeader'] = true;
                    $seccionActual['header'] = trim($linea);
                }
                $seccionActual['lineas'][] = $lineaOriginal;
            }
        }

        if (! empty($seccionActual['lineas'])) {
            $secciones[] = $seccionActual;
        }

        $numSecciones = count($secciones);

        // Función para calcular peso visual aproximado de una línea
        $pesoLinea = function (string $l): float {
            $t = trim($l);
            if ($t === '') {
                return 0.4;
            }
            if (self::isSectionHeader($l)) {
                return 1.6;
            }
            if (self::isLineaDeAcordes($l) || str_contains($l, '[')) {
                return 1.3;
            }

            return 1.0;
        };

        // Calcular peso de cada sección
        $pesosSecciones = [];
        $pesoTotal = 0.0;
        foreach ($secciones as $idx => $sec) {
            $p = 0.0;
            foreach ($sec['lineas'] as $l) {
                $p += $pesoLinea($l);
            }
            $pesosSecciones[$idx] = $p;
            $pesoTotal += $p;
        }

        // 2. Buscar punto de división óptimo donde la sección candidata empiece con encabezado
        $candidatosHeader = [];
        for ($i = 1; $i < $numSecciones; $i++) {
            if ($secciones[$i]['esHeader']) {
                $candidatosHeader[] = $i;
            }
        }

        $mejorIndiceSeccion = null;

        if (! empty($candidatosHeader)) {
            $mejorScore = PHP_FLOAT_MAX;

            foreach ($candidatosHeader as $candIdx) {
                $pesoCol1 = 0.0;
                for ($j = 0; $j < $candIdx; $j++) {
                    $pesoCol1 += $pesosSecciones[$j];
                }
                $pesoCol2 = $pesoTotal - $pesoCol1;

                $diff = abs($pesoCol1 - $pesoCol2);

                // Pequeña preferencia a que columna 1 sea igual o ligeramente mayor que columna 2
                if ($pesoCol1 < $pesoCol2) {
                    $diff += 0.5;
                }

                // Bonificación a secciones principales (Coro, Estrofa, Puente, Verso)
                $hdr = strtolower($secciones[$candIdx]['header'] ?? '');
                if (preg_match('/(coro|chorus|estrofa|verso|verse|puente|bridge)/i', $hdr)) {
                    $diff -= 0.6;
                }

                if ($diff < $mejorScore) {
                    $mejorScore = $diff;
                    $mejorIndiceSeccion = $candIdx;
                }
            }
        }

        // 3. Fallback: Si no hay encabezados de sección a partir del índice 1
        if ($mejorIndiceSeccion === null) {
            // Intentar dividir por estrofas (líneas en blanco)
            $lineasVacias = [];
            foreach ($lineas as $idx => $l) {
                if (trim($l) === '' && $idx > 0 && $idx < $totalLineas - 1) {
                    $lineasVacias[] = $idx;
                }
            }

            if (! empty($lineasVacias)) {
                $mitad = $totalLineas / 2;
                $mejorLineaVacia = null;
                $menorDistancia = PHP_FLOAT_MAX;

                foreach ($lineasVacias as $lv) {
                    $dist = abs($lv - $mitad);
                    if ($dist < $menorDistancia) {
                        $menorDistancia = $dist;
                        $mejorLineaVacia = $lv;
                    }
                }

                $col1 = array_slice($lineas, 0, $mejorLineaVacia);
                $col2 = array_slice($lineas, $mejorLineaVacia + 1);
            } else {
                // Si no hay líneas vacías, dividir por la mitad de líneas
                $corte = (int) ceil($totalLineas / 2);
                $col1 = array_slice($lineas, 0, $corte);
                $col2 = array_slice($lineas, $corte);
            }
        } else {
            // Unir líneas de secciones para columna 1 y columna 2
            $col1 = [];
            for ($i = 0; $i < $mejorIndiceSeccion; $i++) {
                foreach ($secciones[$i]['lineas'] as $l) {
                    $col1[] = $l;
                }
            }

            $col2 = [];
            for ($i = $mejorIndiceSeccion; $i < $numSecciones; $i++) {
                foreach ($secciones[$i]['lineas'] as $l) {
                    $col2[] = $l;
                }
            }
        }

        // 4. Limpiar líneas vacías al final de col1 y al inicio de col2
        while (! empty($col1) && trim(end($col1)) === '') {
            array_pop($col1);
        }

        while (! empty($col2) && trim(reset($col2)) === '') {
            array_shift($col2);
        }

        return [$col1, $col2];
    }

    /**
     * Divide el texto de una canción en dos columnas equilibradas de forma inteligente.
     *
     * @return array{0: string, 1: string}
     */
    public static function dividirTextoEnDosColumnas(string $texto): array
    {
        [$col1, $col2] = self::dividirLineasEnDosColumnas(explode("\n", $texto));

        return [implode("\n", $col1), implode("\n", $col2)];
    }
}
