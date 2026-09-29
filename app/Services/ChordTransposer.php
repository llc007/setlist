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
}
